package com.grappbox.grappbox.timeline_fragment;

import android.accounts.AccountManager;
import android.content.Context;
import android.content.Intent;
import android.database.Cursor;
import android.os.AsyncTask;
import android.os.Bundle;
import android.os.Handler;
import android.support.annotation.Nullable;
import android.support.design.widget.TextInputEditText;
import android.support.v4.app.Fragment;
import android.support.v4.app.LoaderManager;
import android.support.v4.content.CursorLoader;
import android.support.v4.content.Loader;
import android.support.v4.widget.SwipeRefreshLayout;
import android.support.v7.widget.LinearLayoutManager;
import android.support.v7.widget.RecyclerView;
import android.util.Log;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.view.inputmethod.InputMethodManager;
import android.widget.ImageButton;
import android.widget.ImageView;
import android.widget.LinearLayout;
import android.widget.ProgressBar;
import android.widget.TextView;

import com.grappbox.grappbox.ProjectActivity;
import com.grappbox.grappbox.R;
import com.grappbox.grappbox.adapter.TimelineMessageCommentAdapter;
import com.grappbox.grappbox.data.GrappboxContract;
import com.grappbox.grappbox.model.TimelineMessageCommentModel;
import com.grappbox.grappbox.model.TimelineModel;
import com.grappbox.grappbox.receiver.RefreshReceiver;
import com.grappbox.grappbox.singleton.Session;
import com.grappbox.grappbox.sync.GrappboxJustInTimeService;

import java.text.ParseException;
import java.text.SimpleDateFormat;
import java.util.ArrayList;
import java.util.Collection;
import java.util.Collections;
import java.util.Comparator;
import java.util.HashSet;
import java.util.List;

public class TimelineMessageCommentFragment extends Fragment implements LoaderManager.LoaderCallbacks<Cursor>, SwipeRefreshLayout.OnRefreshListener {

    private static final String LOG_TAG = TimelineMessageCommentFragment.class.getSimpleName();
    public static final String EXTRA_PARENT_ID = "parentId";

    private RecyclerView mRecycler;
    private SwipeRefreshLayout mRefresher;
    private RefreshReceiver mRefreshReceiver = null;
    private ProgressBar mLoader;
    private TimelineMessageCommentAdapter mAdapter;
    private TextView    mTimelineMessage;
    private TimelineModel   parent;
    private LinearLayoutManager mLinearLayoutManager;

    public static final int COMMENT_LIMIT = 10;
    public static final int TIMELINE_COMMENT = 0;
    private int loaderPosition = 1;
    private boolean isFirst = true;

    private ImageView           mAvatar;
    private ImageButton         mSend;
    private TextInputEditText   mComment;

    public static final String[] projectionMessage = {
            GrappboxContract.TimelineEntry.TABLE_NAME + "." + GrappboxContract.TimelineEntry._ID,
            GrappboxContract.TimelineEntry.TABLE_NAME + "." + GrappboxContract.TimelineEntry.COLUMN_TYPE_ID,
            GrappboxContract.TimelineCommentEntry.TABLE_NAME + "." + GrappboxContract.TimelineCommentEntry._ID,
            GrappboxContract.TimelineCommentEntry.TABLE_NAME + "." + GrappboxContract.TimelineCommentEntry.COLUMN_PARENT_ID,
            GrappboxContract.TimelineCommentEntry.TABLE_NAME + "." + GrappboxContract.TimelineCommentEntry.COLUMN_GRAPPBOX_ID,
            GrappboxContract.TimelineCommentEntry.TABLE_NAME + "." + GrappboxContract.TimelineCommentEntry.COLUMN_MESSAGE,
            GrappboxContract.TimelineCommentEntry.TABLE_NAME + "." + GrappboxContract.TimelineCommentEntry.COLUMN_LOCAL_CREATOR_ID,
            GrappboxContract.TimelineCommentEntry.TABLE_NAME + "." + GrappboxContract.TimelineCommentEntry.COLUMN_DATE_LAST_EDITED_AT_UTC,
    };

    public TimelineMessageCommentFragment(){
        // Required empty public constructor
    }

    @Nullable
    @Override
    public View onCreateView(LayoutInflater inflater, @Nullable ViewGroup container,
                             @Nullable Bundle savedInstanceState) {
        View view = inflater.inflate(R.layout.timeline_message_comment, container, false);
        parent = getActivity().getIntent().getParcelableExtra(TimelineMessageCommentActivity.EXTRA_TIMELINE_MODEL);
        mLinearLayoutManager = new LinearLayoutManager(getContext());
        mRecycler = (RecyclerView) view.findViewById(R.id.scrollable_content);
        mAdapter = new TimelineMessageCommentAdapter(getActivity(), mLinearLayoutManager);
        mAdapter.setTimelineModel(parent);
        mRecycler.setAdapter(mAdapter);
        mLinearLayoutManager.setReverseLayout(true);
        mRecycler.setLayoutManager(mLinearLayoutManager);

        mTimelineMessage = (TextView) view.findViewById(R.id.message);
        mTimelineMessage.setText(parent._message);
        mRefresher = (SwipeRefreshLayout) view.findViewById(R.id.refresh);
        mLoader = (ProgressBar) view.findViewById(R.id.loader);
        mAdapter.registerAdapterDataObserver(new AdapterObserver());

        mRefreshReceiver = new RefreshReceiver(new Handler(), mRefresher, getActivity());
        mRefresher.setOnRefreshListener(this);
        mLoader.setVisibility(View.GONE);
        mRefresher.setVisibility(View.VISIBLE);
        mAdapter.setRefreshReceiver(mRefreshReceiver);
        mAvatar = (ImageView) view.findViewById(R.id.avatar);
        mSend = (ImageButton) view.findViewById(R.id.reply);
        mComment = (TextInputEditText) view.findViewById(R.id.comment);
        mSend.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                AccountManager am = AccountManager.get(getContext());
                final String token = am.getUserData(Session.getInstance(getContext()).getCurrentAccount(), GrappboxJustInTimeService.EXTRA_API_TOKEN);
                Cursor cursorTimelineId = getContext().getContentResolver().query(GrappboxContract.TimelineMessageEntry.CONTENT_URI,
                        new String[] {GrappboxContract.TimelineEntry.TABLE_NAME + "." + GrappboxContract.TimelineEntry._ID},
                        GrappboxContract.TimelineMessageEntry.TABLE_NAME + "." + GrappboxContract.TimelineMessageEntry.COLUMN_GRAPPBOX_ID + " =?",
                        new String[]{String.valueOf(parent._grappboxId)},
                        null);

                if (cursorTimelineId == null || !cursorTimelineId.moveToFirst())
                    return;

                Intent addComment = new Intent(getContext(), GrappboxJustInTimeService.class);
                mAdapter.setACtion(TimelineMessageCommentAdapter.ACTION_ADD);
                addComment.setAction(GrappboxJustInTimeService.ACTION_TIMELINE_ADD_COMMENT);
                addComment.putExtra(GrappboxJustInTimeService.EXTRA_API_TOKEN, token);
                addComment.putExtra(GrappboxJustInTimeService.EXTRA_TIMELINE_MESSAGE, mComment.getText().toString());
                addComment.putExtra(GrappboxJustInTimeService.EXTRA_TIMELINE_PARENT_ID, Integer.valueOf(parent._grappboxId));
                addComment.putExtra(GrappboxJustInTimeService.EXTRA_TIMELINE_ID, cursorTimelineId.getLong(0));
                addComment.putExtra(GrappboxJustInTimeService.EXTRA_RESPONSE_RECEIVER, mRefreshReceiver);
                addComment.putExtra(GrappboxJustInTimeService.EXTRA_TIMELINE_IS_COMMENT, true);
                getContext().startService(addComment);
                cursorTimelineId.close();
                mComment.setText("");
                InputMethodManager imm = (InputMethodManager)getContext().getSystemService(Context.INPUT_METHOD_SERVICE);
                imm.hideSoftInputFromWindow(getActivity().getCurrentFocus().getWindowToken(), 0);
                mLinearLayoutManager.scrollToPosition(0);
            }
        });
        mRecycler.addOnScrollListener(new RecyclerView.OnScrollListener() {
            @Override
            public void onScrolled(RecyclerView recyclerView, int dx, int dy) {
                super.onScrolled(recyclerView, dx, dy);
                if (dy > 0) {
                    int pastVisible = mLinearLayoutManager.findFirstVisibleItemPosition();
                    if (pastVisible < loaderPosition){
                        initLoader();
                        loaderPosition -= COMMENT_LIMIT;
                    }
                } else if (dy < 0) {
                    int visibleItemCount = mLinearLayoutManager.getChildCount();
                    int totalItemCount = mLinearLayoutManager.getItemCount();
                    int pastVisible = mLinearLayoutManager.findFirstVisibleItemPosition();
                    if ((visibleItemCount + pastVisible) >= totalItemCount){
                        initLoader();
                        loaderPosition += COMMENT_LIMIT;
                    }

                }
            }
        });
        getLoaderManager().initLoader(TIMELINE_COMMENT, null, this);
        return view;
    }

    public void initLoader()
    {
        getLoaderManager().restartLoader(TIMELINE_COMMENT, null, this);
    }

    @Override
    public void onRefresh() {
        long projectId = getActivity().getIntent().getLongExtra(ProjectActivity.EXTRA_PROJECT_ID, -1);

        Cursor cursorTimelineId = getActivity().getContentResolver().query(GrappboxContract.TimelineEntry.CONTENT_URI,
                new String[] {GrappboxContract.TimelineEntry.TABLE_NAME + "." + GrappboxContract.TimelineEntry._ID},
                GrappboxContract.TimelineEntry.TABLE_NAME + "." + GrappboxContract.TimelineEntry.COLUMN_LOCAL_PROJECT_ID + "=? AND "
                        + GrappboxContract.TimelineEntry.TABLE_NAME + "." + GrappboxContract.TimelineEntry.COLUMN_TYPE_ID + "=?",
                new String[]{String.valueOf(projectId), String.valueOf(parent._timelineType)},
                null);
        if (cursorTimelineId == null || !cursorTimelineId.moveToFirst())
            return;
        Intent timelineSync = new Intent(getActivity(), GrappboxJustInTimeService.class);
        timelineSync.setAction(GrappboxJustInTimeService.ACTION_SYNC_TIMELINE_COMMENTS);
        timelineSync.putExtra(GrappboxJustInTimeService.EXTRA_TIMELINE_ID, cursorTimelineId.getLong(0));
        timelineSync.putExtra(GrappboxJustInTimeService.EXTRA_TIMELINE_PARENT_ID, Long.valueOf(parent._grappboxId));
        timelineSync.putExtra(GrappboxJustInTimeService.EXTRA_RESPONSE_RECEIVER, mRefreshReceiver);
        getActivity().startService(timelineSync);
        cursorTimelineId.close();
    }

    @Override
    public Loader<Cursor> onCreateLoader(int id, Bundle args) {
        int itemPosition = mLinearLayoutManager.findFirstVisibleItemPosition();
        int valueOffset = itemPosition - COMMENT_LIMIT;
        if (valueOffset < 0)
            valueOffset = 0;
        String offset = String.valueOf(valueOffset);
        String limit = String.valueOf(itemPosition + COMMENT_LIMIT);
        String sortOrder = "datetime(" + GrappboxContract.TimelineCommentEntry.COLUMN_DATE_LAST_EDITED_AT_UTC +
                ") DESC LIMIT " + offset + ", " + limit;
        String selection;
        String[] selectionArgs;
        long lpid = getActivity().getIntent().getLongExtra(ProjectActivity.EXTRA_PROJECT_ID, -1);

        switch (id) {
            case TIMELINE_COMMENT:
                selection = GrappboxContract.TimelineEntry.TABLE_NAME + "." + GrappboxContract.TimelineEntry.COLUMN_LOCAL_PROJECT_ID + "=? AND "
                + GrappboxContract.TimelineCommentEntry.TABLE_NAME + "." + GrappboxContract.TimelineCommentEntry.COLUMN_PARENT_ID + "=?";
                selectionArgs = new String[] {
                    String.valueOf(lpid),
                    parent._grappboxId
                };
                break;

            default:
                throw new IllegalArgumentException("Type doesn't exist");
        }
        return new CursorLoader(getActivity(), GrappboxContract.TimelineCommentEntry.CONTENT_URI, projectionMessage, selection, selectionArgs, sortOrder);
    }

    @Override
    public void onLoadFinished(Loader<Cursor> loader, Cursor data) {
        if (loader.getId() == TIMELINE_COMMENT) {
            if (data == null || !data.moveToFirst())
                return;
            List<TimelineMessageCommentModel> models = new ArrayList<>();
            do {
                models.add(new TimelineMessageCommentModel(getActivity(), data));
            } while (data.moveToNext());
            mAdapter.mergeItem(models);
            if (isFirst) {
                mLinearLayoutManager.scrollToPosition(mAdapter.getSize());
                isFirst = false;
            }
        }
    }

    @Override
    public void onLoaderReset(Loader<Cursor> loader) {
        loader.forceLoad();
    }

    class AdapterObserver extends RecyclerView.AdapterDataObserver {
        @Override
        public void onChanged() {
            super.onChanged();
        }
    }
}
