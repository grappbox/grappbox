package com.grappbox.grappbox.timeline_fragment;

import android.accounts.AccountManager;
import android.app.Activity;
import android.content.ContentValues;
import android.content.DialogInterface;
import android.content.Intent;
import android.database.Cursor;
import android.database.DatabaseUtils;
import android.database.MatrixCursor;
import android.database.MergeCursor;
import android.database.sqlite.SQLiteDatabase;
import android.os.AsyncTask;
import android.os.Bundle;
import android.os.Handler;
import android.support.annotation.Nullable;
import android.support.design.widget.FloatingActionButton;
import android.support.v4.app.Fragment;
import android.support.v4.app.LoaderManager;
import android.support.v4.content.ContextCompat;
import android.support.v4.content.CursorLoader;
import android.support.v4.content.Loader;
import android.support.v4.widget.CursorAdapter;
import android.support.v4.widget.SwipeRefreshLayout;
import android.support.v7.app.AlertDialog;
import android.support.v7.widget.LinearLayoutManager;
import android.support.v7.widget.RecyclerView;
import android.util.Log;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.view.animation.Animation;
import android.view.animation.Transformation;
import android.widget.EditText;
import android.widget.ListView;
import android.widget.ProgressBar;

import com.grappbox.grappbox.ProjectActivity;
import com.grappbox.grappbox.R;
import com.grappbox.grappbox.Utils;
import com.grappbox.grappbox.adapter.TimelineListAdapter;
import com.grappbox.grappbox.data.GrappboxContract;
import com.grappbox.grappbox.data.GrappboxContract.TimelineEntry;
import com.grappbox.grappbox.data.GrappboxContract.TimelineMessageEntry;
import com.grappbox.grappbox.data.GrappboxDBHelper;
import com.grappbox.grappbox.item_decoration.HorizontalDivider;
import com.grappbox.grappbox.model.TimelineModel;
import com.grappbox.grappbox.receiver.RefreshReceiver;
import com.grappbox.grappbox.singleton.Session;
import com.grappbox.grappbox.sync.GrappboxJustInTimeService;

import java.sql.Time;
import java.text.DateFormat;
import java.text.ParseException;
import java.text.SimpleDateFormat;
import java.util.ArrayList;
import java.util.Collection;
import java.util.Collections;
import java.util.Comparator;
import java.util.HashSet;
import java.util.List;

public class TimelineListFragment extends Fragment implements LoaderManager.LoaderCallbacks<Cursor>, SwipeRefreshLayout.OnRefreshListener {

    public static final String ARG_LIST_TYPE = "com.grappbox.grappbox.timeline_fragment.ARG_LIST_TYPE";
    public static final String LOG_TAG = TimelineListFragment.class.getSimpleName();

    public static final int TIMELINE_TEAM = 0;
    public static final int TIMELINE_CLIENT = 1;

    public static final int TIMELINE_LIMIT = 10;

    private static final String TIMELINE_BUNDLE_OFFSET = "com.grappbox.grappbox.timeline_fragment.BUNDLE_OFFSET";
    private static final String TIMELINE_BUNDLE_LIMIT = "com.grappbox.grappbox.timeline_fragment.BUNDLE_LIMIT";

    public static final String[] projectionMessage = {
            TimelineEntry.TABLE_NAME + "." + TimelineEntry._ID,
            TimelineEntry.TABLE_NAME + "." + TimelineEntry.COLUMN_TYPE_ID,
            TimelineMessageEntry.TABLE_NAME + "." + TimelineMessageEntry._ID,
            TimelineMessageEntry.TABLE_NAME + "." + TimelineMessageEntry.COLUMN_GRAPPBOX_ID,
            TimelineMessageEntry.TABLE_NAME + "." + TimelineMessageEntry.COLUMN_TITLE,
            TimelineMessageEntry.TABLE_NAME + "." + TimelineMessageEntry.COLUMN_MESSAGE,
            TimelineMessageEntry.TABLE_NAME + "." + TimelineMessageEntry.COLUMN_DATE_LAST_EDITED_AT_UTC,
            TimelineMessageEntry.TABLE_NAME + "." + TimelineMessageEntry.COLUMN_DATE_DELETED_AT_UTC,
            TimelineMessageEntry.TABLE_NAME + "." + TimelineMessageEntry.COLUMN_COUNT_ANSWER,
            TimelineMessageEntry.TABLE_NAME + "." + TimelineMessageEntry.COLUMN_PARENT_ID,
            TimelineMessageEntry.TABLE_NAME + "." + TimelineMessageEntry.COLUMN_LOCAL_CREATOR_ID
    };

    public static final String[] projectionMessageRow = {
            TimelineEntry._ID,
            TimelineEntry.COLUMN_TYPE_ID,
            TimelineMessageEntry._ID,
            TimelineMessageEntry.COLUMN_GRAPPBOX_ID,
            TimelineMessageEntry.COLUMN_TITLE,
            TimelineMessageEntry.COLUMN_MESSAGE,
            TimelineMessageEntry.COLUMN_DATE_LAST_EDITED_AT_UTC,
            TimelineMessageEntry.COLUMN_DATE_DELETED_AT_UTC,
            TimelineMessageEntry.COLUMN_COUNT_ANSWER,
            TimelineMessageEntry.COLUMN_PARENT_ID,
            TimelineMessageEntry.COLUMN_LOCAL_CREATOR_ID
    };

    private FloatingActionButton mAddMessage;

    private int mTimelineTypeId = 0;
    private TimelineListAdapter mAdapter;
    private SwipeRefreshLayout mRefresher;
    private ProgressBar mLoader;
    private RefreshReceiver mRefreshReceiver = null;
    private RecyclerView mTimelineList;
    private LinearLayoutManager mLinearLayoutManager;
    private MatrixCursor mMatrixCursor;

    public TimelineListFragment(){
        // Required empty public constructor
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
        mAddMessage = (FloatingActionButton) getActivity().findViewById(R.id.fab);
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

                        if (title == null || message == null) {
                            dialog.cancel();
                            return;
                        }
                        Cursor cursorTimelineId = getActivity().getContentResolver().query(TimelineEntry.CONTENT_URI,
                                new String[] {TimelineEntry.TABLE_NAME + "." + TimelineEntry.COLUMN_GRAPPBOX_ID},
                                TimelineEntry.TABLE_NAME + "." +  TimelineEntry.COLUMN_LOCAL_PROJECT_ID + "=? AND " + TimelineEntry.TABLE_NAME + "." +  TimelineEntry.COLUMN_TYPE_ID + " =?",
                                new String[]{String.valueOf(getActivity().getIntent().getLongExtra(ProjectActivity.EXTRA_PROJECT_ID, -1)), String.valueOf(mTimelineTypeId)},
                                null);
                        if (cursorTimelineId == null || !cursorTimelineId.moveToFirst())
                            return;
                        Intent addMessage = new Intent(getActivity(), GrappboxJustInTimeService.class);
                        addMessage.setAction(GrappboxJustInTimeService.ACTION_TIMELINE_ADD_MESSAGE);
                        addMessage.putExtra(GrappboxJustInTimeService.EXTRA_API_TOKEN, token);
                        addMessage.putExtra(GrappboxJustInTimeService.EXTRA_TIMELINE_ID, cursorTimelineId.getLong(0));
                        addMessage.putExtra(GrappboxJustInTimeService.EXTRA_TIMELINE_TITLE, title.getText().toString());
                        addMessage.putExtra(GrappboxJustInTimeService.EXTRA_TIMELINE_MESSAGE, message.getText().toString());
                        addMessage.putExtra(GrappboxJustInTimeService.EXTRA_OFFSET, 0);
                        addMessage.putExtra(GrappboxJustInTimeService.EXTRA_LIMIT, mAdapter.getItemCount() + 1);
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
        mMatrixCursor = new MatrixCursor(projectionMessageRow);
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
        String offset = String.valueOf(mAdapter.getItemCount());
        String limit = String.valueOf(TIMELINE_LIMIT);
        if (args != null){
            offset = args.getString(TIMELINE_BUNDLE_OFFSET);
            limit = args.getString(TIMELINE_BUNDLE_LIMIT);
        }
        Log.v(LOG_TAG, "OnCreateLoader, offset : " + offset + ", limit : " + limit);
        String sortOrder = "date(" + TimelineMessageEntry.COLUMN_DATE_LAST_EDITED_AT_UTC + ") ASC LIMIT " +
                offset + ", " + limit;
        String selection;
        String[] selectionArgs;
        long lpid = getActivity().getIntent().getLongExtra(ProjectActivity.EXTRA_PROJECT_ID, -1);

        switch (id){
            case TIMELINE_TEAM:
                selection = TimelineEntry.TABLE_NAME + "." +TimelineEntry.COLUMN_LOCAL_PROJECT_ID + "=? AND "
                        + TimelineEntry.TABLE_NAME + "." + TimelineEntry.COLUMN_TYPE_ID + "=? AND "
                        + TimelineMessageEntry.TABLE_NAME + "." + TimelineMessageEntry.COLUMN_PARENT_ID + "=?";
                selectionArgs = new String[]{
                        String.valueOf(lpid),
                        String.valueOf(TIMELINE_TEAM + 1),
                        "null"
                };
                mTimelineTypeId = TIMELINE_TEAM;
                break;

            case TIMELINE_CLIENT:
                selection = TimelineEntry.TABLE_NAME + "." +  TimelineEntry.COLUMN_LOCAL_PROJECT_ID + "=? AND "
                        + TimelineEntry.TABLE_NAME + "." + TimelineEntry.COLUMN_TYPE_ID + "=? AND "
                        + TimelineMessageEntry.TABLE_NAME + "." + TimelineMessageEntry.COLUMN_PARENT_ID + "=?";
                selectionArgs = new String[]{
                        String.valueOf(lpid),
                        String.valueOf(TIMELINE_CLIENT + 1),
                        "null"
                };
                mTimelineTypeId = TIMELINE_CLIENT;
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

        Intent timelineSync = new Intent(getActivity(), GrappboxJustInTimeService.class);
        timelineSync.setAction(GrappboxJustInTimeService.ACTION_SYNC_TIMELINE_MESSAGES);

        Cursor cursorTimelineId = getActivity().getContentResolver().query(TimelineEntry.CONTENT_URI,
                new String[] {TimelineEntry.TABLE_NAME + "." + TimelineEntry.COLUMN_GRAPPBOX_ID,
                        TimelineEntry.TABLE_NAME + "." + TimelineEntry.COLUMN_TYPE_ID},
                TimelineEntry.TABLE_NAME + "." +  TimelineEntry.COLUMN_LOCAL_PROJECT_ID + "=?",
                new String[]{String.valueOf(projectId)},
                null);
        if (cursorTimelineId == null || !cursorTimelineId.moveToFirst())
            return;
        timelineSync.putExtra(GrappboxJustInTimeService.EXTRA_RESPONSE_RECEIVER, mRefreshReceiver);
        timelineSync.putExtra(GrappboxJustInTimeService.EXTRA_TIMELINE_ID, cursorTimelineId.getLong(0));
        timelineSync.putExtra(GrappboxJustInTimeService.EXTRA_USER_ID, uid);
        timelineSync.putExtra(GrappboxJustInTimeService.EXTRA_PROJECT_ID, projectId);
        timelineSync.putExtra(GrappboxJustInTimeService.EXTRA_LIMIT, limit);
        timelineSync.putExtra(GrappboxJustInTimeService.EXTRA_OFFSET, offset);
        getActivity().startService(timelineSync);
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
        if (data != null && !data.moveToFirst()) {
            //updateDataBase();
            return;
        }
        /*
        if (mAdapter.getCursor() == null) {
            mAdapter.setCursor(data);
            message = data;
        } else {

            if (mAdapter.getCursor().moveToFirst()) {
                Log.v(LOG_TAG, "mergeCursor start");
                ArrayList<Cursor> finals = new ArrayList<>();
                finals.add(data);
                finals.add(mAdapter.getCursor());
                Cursor[] finalArray = finals.toArray(new Cursor[finals.size()]);
                message = new MergeCursor(finalArray);
                data = new MergeCursor(finalArray);
                mAdapter.setCursor(data);
            }
        }
        if (message != null && !message.moveToFirst()) {
            return;
        }*/

        List<TimelineModel> models = new ArrayList<>();
        do {
            models.add(new TimelineModel(getActivity(), data));
        } while (data.moveToNext());
        Collections.sort(models, new StringDateComparator());
        AdditionalDataLoader task = new AdditionalDataLoader();
        task.execute(models);
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

    private class AdditionalDataLoader extends AsyncTask<Collection<TimelineModel>, Void, Collection<TimelineModel>> {

        @Override
        protected void onPostExecute(Collection<TimelineModel> timelineModels) {
            super.onPostExecute(timelineModels);
            mAdapter.clear();
            mAdapter.add(timelineModels);
        }

        @Override
        protected Collection<TimelineModel> doInBackground(Collection<TimelineModel>... params) {
            if (params == null || params.length < 1)
                throw new IllegalArgumentException();

            return params[0];
        }
    }

    class StringDateComparator implements Comparator<TimelineModel>
    {

        SimpleDateFormat dateFormat = new SimpleDateFormat("dd/MM/yyyy");

        @Override
        public int compare(TimelineModel o1, TimelineModel o2) {

            try {
                return dateFormat.parse(o2._lastUpadte).compareTo(dateFormat.parse(o1._lastUpadte));
            } catch (ParseException e) {
                e.printStackTrace();
            }
            return -1;
        }
    }
}
