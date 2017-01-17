package com.grappbox.grappbox.timeline_fragment;

import android.accounts.AccountManager;
import android.content.DialogInterface;
import android.content.Intent;
import android.database.Cursor;
import android.os.Bundle;
import android.os.Handler;
import android.support.annotation.Nullable;
import android.support.design.widget.FloatingActionButton;
import android.support.v4.app.Fragment;
import android.support.v4.app.LoaderManager;
import android.support.v4.content.ContextCompat;
import android.support.v4.content.CursorLoader;
import android.support.v4.content.Loader;
import android.support.v4.widget.SwipeRefreshLayout;
import android.support.v7.app.AlertDialog;
import android.support.v7.widget.LinearLayoutManager;
import android.support.v7.widget.RecyclerView;
import android.util.Log;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.EditText;
import android.widget.ProgressBar;

import com.grappbox.grappbox.ProjectActivity;
import com.grappbox.grappbox.R;
import com.grappbox.grappbox.adapter.TimelineListAdapter;
import com.grappbox.grappbox.data.GrappboxContract.TimelineEntry;
import com.grappbox.grappbox.data.GrappboxContract.TimelineMessageEntry;
import com.grappbox.grappbox.item_decoration.HorizontalDivider;
import com.grappbox.grappbox.model.TimelineModel;
import com.grappbox.grappbox.receiver.RefreshReceiver;
import com.grappbox.grappbox.singleton.Session;
import com.grappbox.grappbox.sync.GrappboxJustInTimeService;

import java.util.ArrayList;
import java.util.List;

public class TimelineListFragment extends Fragment implements LoaderManager.LoaderCallbacks<Cursor>, SwipeRefreshLayout.OnRefreshListener {

    public static final String ARG_LIST_TYPE = "com.grappbox.grappbox.timeline_fragment.ARG_LIST_TYPE";
    public static final String LOG_TAG = TimelineListFragment.class.getSimpleName();

    public static final int TIMELINE_TEAM = 1;
    public static final int TIMELINE_CLIENT = 0;

    public static final int TIMELINE_LIMIT = 10;

    public static final String[] projectionMessage = {
            TimelineEntry.TABLE_NAME + "." + TimelineEntry._ID,
            TimelineEntry.TABLE_NAME + "." + TimelineEntry.COLUMN_TYPE_ID,
            TimelineMessageEntry.TABLE_NAME + "." + TimelineMessageEntry._ID,
            TimelineMessageEntry.TABLE_NAME + "." + TimelineMessageEntry.COLUMN_GRAPPBOX_ID,
            TimelineMessageEntry.TABLE_NAME + "." + TimelineMessageEntry.COLUMN_TITLE,
            TimelineMessageEntry.TABLE_NAME + "." + TimelineMessageEntry.COLUMN_MESSAGE,
            TimelineMessageEntry.TABLE_NAME + "." + TimelineMessageEntry.COLUMN_DATE_LAST_EDITED_AT_UTC,
            TimelineMessageEntry.TABLE_NAME + "." + TimelineMessageEntry.COLUMN_COUNT_ANSWER,
            TimelineMessageEntry.TABLE_NAME + "." + TimelineMessageEntry.COLUMN_LOCAL_CREATOR_ID
    };

    private FloatingActionButton mAddMessage;

    private int mTimelineTypeId;
    private TimelineListAdapter mAdapter;
    private SwipeRefreshLayout mRefresher;
    private ProgressBar mLoader;
    private RefreshReceiver mRefreshReceiver = null;
    private RecyclerView mTimelineList;
    private LinearLayoutManager mLinearLayoutManager;
    private int loaderPosition = 0;

    public TimelineListFragment(){
    }

    @Override
    public void onActivityCreated(@Nullable Bundle savedInstanceState) {
        super.onActivityCreated(savedInstanceState);
        getLoaderManager().initLoader(getArguments().getInt(ARG_LIST_TYPE), null, this);
    }

    @Override
    public View onCreateView(LayoutInflater inflater, ViewGroup container,
                             Bundle savedInstanceState) {
        View v = inflater.inflate(R.layout.fragment_timeline_list, container, false);
        mTimelineTypeId = getArguments().getInt(ARG_LIST_TYPE);
        mTimelineList = (RecyclerView) v.findViewById(R.id.timelinelist);
        mAdapter = new TimelineListAdapter(getActivity(), mTimelineList);
        mTimelineList.setAdapter(mAdapter);
        mLinearLayoutManager = new LinearLayoutManager(getActivity());
        mTimelineList.addItemDecoration(new HorizontalDivider(ContextCompat.getColor(getActivity(), R.color.GrappGrayMedium)));
        mTimelineList.setLayoutManager(mLinearLayoutManager);

        mTimelineList.addOnScrollListener(new RecyclerView.OnScrollListener() {

            @Override
            public void onScrolled(RecyclerView recyclerView, int dx, int dy) {
                super.onScrolled(recyclerView, dx, dy);
                if (dy > 0) {
                    int visibleItemCount = mLinearLayoutManager.getChildCount();
                    int totalItemCount = mLinearLayoutManager.getItemCount();
                    int pastVisible = mLinearLayoutManager.findFirstVisibleItemPosition();
                    if ((visibleItemCount + pastVisible) >= totalItemCount){
                        initLoader();
                        loaderPosition += TIMELINE_LIMIT;
                    }
                } else if (dy < 0) {
                    int pastVisible = mLinearLayoutManager.findFirstVisibleItemPosition();
                    if (pastVisible <= loaderPosition){
                        initLoader();
                        loaderPosition -= TIMELINE_LIMIT;
                    }
                }
            }
        });
        mRefresher = (SwipeRefreshLayout) v.findViewById(R.id.refresh);
        mLoader = (ProgressBar) v.findViewById(R.id.loader);
        mAdapter.registerAdapterDataObserver(new AdapterObserver());

        mRefreshReceiver = new RefreshReceiver(new Handler(), mRefresher, getActivity());
        mRefresher.setOnRefreshListener(this);
        if (savedInstanceState != null)
            mRefresher.setVisibility(View.VISIBLE);
        mAdapter.setRefreshReciver(mRefreshReceiver);
        mAddMessage = (FloatingActionButton) v.findViewById(R.id.fab);
        mAddMessage.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                AlertDialog.Builder builder = new AlertDialog.Builder(getActivity());
                final View dialogView = LayoutInflater.from(getActivity()).inflate(R.layout.dialog_timeline_add_message, null);
                AccountManager am = AccountManager.get(getActivity());
                final String token = am.getUserData(Session.getInstance(getActivity()).getCurrentAccount(), GrappboxJustInTimeService.EXTRA_API_TOKEN);

                builder.setTitle(R.string.add_message);
                builder.setView(dialogView);
                builder.setPositiveButton(getActivity().getString(R.string.positive_response), new DialogInterface.OnClickListener() {
                    @Override
                    public void onClick(DialogInterface dialogInterface, int which) {
                        AlertDialog dialog = (AlertDialog) dialogInterface;
                        EditText title = (EditText) dialog.findViewById(R.id.input_title);
                        EditText message = (EditText) dialog.findViewById(R.id.input_content);
                        if (title == null || message == null ||
                                title.getText().toString().equals("") || message.getText().toString().equals("")) {
                            dialog.cancel();
                            return;
                        }
                        Cursor cursorTimelineId = getActivity().getContentResolver().query(TimelineEntry.CONTENT_URI,
                                new String[] {TimelineEntry.TABLE_NAME + "." + TimelineEntry._ID},
                                TimelineEntry.TABLE_NAME + "." +  TimelineEntry.COLUMN_LOCAL_PROJECT_ID + "=? AND " + TimelineEntry.TABLE_NAME + "." +  TimelineEntry.COLUMN_TYPE_ID + " =?",
                                new String[]{String.valueOf(getActivity().getIntent().getLongExtra(ProjectActivity.EXTRA_PROJECT_ID, -1)), String.valueOf(mTimelineTypeId + 1)},
                                null);
                        if (cursorTimelineId == null || !cursorTimelineId.moveToFirst())
                            return;
                        Intent addMessage = new Intent(getActivity(), GrappboxJustInTimeService.class);
                        addMessage.setAction(GrappboxJustInTimeService.ACTION_TIMELINE_ADD_MESSAGE);
                        addMessage.putExtra(GrappboxJustInTimeService.EXTRA_API_TOKEN, token);
                        addMessage.putExtra(GrappboxJustInTimeService.EXTRA_TIMELINE_ID, cursorTimelineId.getLong(0));
                        addMessage.putExtra(GrappboxJustInTimeService.EXTRA_TIMELINE_TITLE, title.getText().toString());
                        addMessage.putExtra(GrappboxJustInTimeService.EXTRA_TIMELINE_MESSAGE, message.getText().toString());
                        getActivity().startService(addMessage);
                        cursorTimelineId.close();
                    }
                });
                builder.setNegativeButton(getActivity().getString(R.string.negative_response), new DialogInterface.OnClickListener() {
                    @Override
                    public void onClick(DialogInterface dialog, int which) {
                        dialog.dismiss();
                    }
                });
                builder.show();
            }
        });

        return v;
    }

    @Override
    public void onResume() {
        super.onResume();
    }

    private void initLoader()
    {
        getLoaderManager().restartLoader(mTimelineTypeId, null, this);
    }

    @Override
    public Loader<Cursor> onCreateLoader(int id, Bundle args) {
        int position = mLinearLayoutManager.findFirstVisibleItemPosition();
        int offsetValue = position - TIMELINE_LIMIT;
        if (offsetValue < 0)
            offsetValue = 0;

        String offset = String.valueOf(offsetValue);
        String limit = String.valueOf(position + TIMELINE_LIMIT);

        String sortOrder = "date(" + TimelineMessageEntry.COLUMN_DATE_LAST_EDITED_AT_UTC + ") DESC LIMIT " +
                offset + ", " + limit;
        String selection;
        String[] selectionArgs;
        Log.v(LOG_TAG, "onCreateLoader");
        long lpid = getActivity().getIntent().getLongExtra(ProjectActivity.EXTRA_PROJECT_ID, -1);
        switch (id){
            case TIMELINE_TEAM:
                selection = TimelineEntry.TABLE_NAME + "." +TimelineEntry.COLUMN_LOCAL_PROJECT_ID + "=? AND "
                        + TimelineEntry.TABLE_NAME + "." + TimelineEntry.COLUMN_TYPE_ID + "=?";
                selectionArgs = new String[]{
                        String.valueOf(lpid),
                        String.valueOf(2)
                };
                break;

            case TIMELINE_CLIENT:
                selection = TimelineEntry.TABLE_NAME + "." +  TimelineEntry.COLUMN_LOCAL_PROJECT_ID + "=? AND "
                        + TimelineEntry.TABLE_NAME + "." + TimelineEntry.COLUMN_TYPE_ID + "=?";
                selectionArgs = new String[]{
                        String.valueOf(lpid),
                        String.valueOf(1)
                };
                break;

            default:
                throw new IllegalArgumentException("Type doesn't exist");
        }
        return new CursorLoader(getActivity(), TimelineMessageEntry.CONTENT_URI, projectionMessage, selection, selectionArgs, sortOrder);
    }

    private void RefreshDatabase(int offset, int limit){
        AccountManager am = AccountManager.get(getActivity());
        long projectId = getActivity().getIntent().getLongExtra(ProjectActivity.EXTRA_PROJECT_ID, -1);
        long uid = Long.parseLong(am.getUserData(Session.getInstance(getActivity()).getCurrentAccount(), GrappboxJustInTimeService.EXTRA_USER_ID));
        Cursor cursorTimelineId = getActivity().getContentResolver().query(TimelineEntry.CONTENT_URI,
                new String[] {TimelineEntry.TABLE_NAME + "." + TimelineEntry._ID},
                TimelineEntry.TABLE_NAME + "." +TimelineEntry.COLUMN_LOCAL_PROJECT_ID + "=? AND "
                + TimelineEntry.TABLE_NAME + "." + TimelineEntry.COLUMN_TYPE_ID + "=?",
                new String[]{String.valueOf(projectId), String.valueOf(mTimelineTypeId)},
                null);
        if (cursorTimelineId == null || !cursorTimelineId.moveToFirst())
            return;

        Log.v(LOG_TAG, "refresh data");
        Intent timelineSync = new Intent(getActivity(), GrappboxJustInTimeService.class);
        timelineSync.setAction(GrappboxJustInTimeService.ACTION_SYNC_TIMELINE_MESSAGES);
        timelineSync.putExtra(GrappboxJustInTimeService.EXTRA_RESPONSE_RECEIVER, mRefreshReceiver);
        timelineSync.putExtra(GrappboxJustInTimeService.EXTRA_TIMELINE_ID, cursorTimelineId.getLong(0));
        timelineSync.putExtra(GrappboxJustInTimeService.EXTRA_USER_ID, uid);
        timelineSync.putExtra(GrappboxJustInTimeService.EXTRA_PROJECT_ID, projectId);
        timelineSync.putExtra(GrappboxJustInTimeService.EXTRA_LIMIT, limit);
        timelineSync.putExtra(GrappboxJustInTimeService.EXTRA_OFFSET, offset);
        getActivity().startService(timelineSync);
        cursorTimelineId.close();
    }

    @Override
    public void onRefresh() {
        RefreshDatabase(0, mAdapter.getItemCount() == 0 ? TIMELINE_LIMIT : mAdapter.getItemCount());
    }

    private void updateDataBase()
    {
        RefreshDatabase(mAdapter.getItemCount(), TIMELINE_LIMIT);
    }

    @Override
    public void onLoadFinished(Loader<Cursor> loader, Cursor data) {
        if (data == null || !data.moveToFirst()) {
            updateDataBase();
            return;
        }
        List<TimelineModel> models = new ArrayList<>();
        do {
            models.add(new TimelineModel(getActivity(), data));
        } while (data.moveToNext());
        mAdapter.mergeItem(models);
    }

    @Override
    public void onLoaderReset(Loader<Cursor> loader) {
        loader.forceLoad();
    }

    class AdapterObserver extends RecyclerView.AdapterDataObserver {

        @Override
        public void onChanged() {
            super.onChanged();
            if (!mAdapter.isEmpty()){
                mLoader.setVisibility(View.GONE);
                mRefresher.setVisibility(View.VISIBLE);
            }
        }
    }
}
