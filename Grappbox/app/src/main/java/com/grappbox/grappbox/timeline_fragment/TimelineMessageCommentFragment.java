package com.grappbox.grappbox.timeline_fragment;

import android.database.Cursor;
import android.os.Bundle;
import android.os.Handler;
import android.support.annotation.Nullable;
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
import android.widget.ProgressBar;
import android.widget.TextView;

import com.grappbox.grappbox.ProjectActivity;
import com.grappbox.grappbox.R;
import com.grappbox.grappbox.adapter.TimelineMessageCommentAdapter;
import com.grappbox.grappbox.data.GrappboxContract;
import com.grappbox.grappbox.model.TimelineModel;
import com.grappbox.grappbox.receiver.RefreshReceiver;

import java.sql.Time;

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

    public static final int TIMELINE_LOADER = 0;
    public static final int TIMELINE_COMMENT = 0;
    public static final int TIMELINE_OFFSET = 30;

    public TimelineMessageCommentFragment(){
        // Required empty public constructor
    }

    @Nullable
    @Override
    public View onCreateView(LayoutInflater inflater, @Nullable ViewGroup container,
                             @Nullable Bundle savedInstanceState) {
        View view = inflater.inflate(R.layout.timeline_message_comment, container, false);
        parent = getActivity().getIntent().getParcelableExtra(TimelineMessageCommentActivity.EXTRA_TIMELINE_MODEL);
        mRecycler = (RecyclerView) view.findViewById(R.id.scrollable_content);
        mAdapter = new TimelineMessageCommentAdapter(getActivity());
        mAdapter.setTimelineModel(parent);
        mRecycler.setAdapter(mAdapter);
        mRecycler.setLayoutManager(new LinearLayoutManager(getActivity()));
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
        this.getLoaderManager().initLoader(TIMELINE_LOADER, null, this);
        return view;
    }

    @Override
    public void onRefresh() {

    }

    @Override
    public Loader<Cursor> onCreateLoader(int id, Bundle args) {
        Log.v(LOG_TAG, "create loader");
        String sortOrder = "datetime(" + GrappboxContract.TimelineMessageEntry.COLUMN_DATE_LAST_EDITED_AT_UTC + ") DESC LIMIT 30";
        String selection;
        String[] selectionArgs;
        long lpid = getActivity().getIntent().getLongExtra(ProjectActivity.EXTRA_PROJECT_ID, -1);

        switch (id) {
            case TIMELINE_COMMENT:
                selection = GrappboxContract.TimelineEntry.TABLE_NAME + "." + GrappboxContract.TimelineEntry.COLUMN_LOCAL_PROJECT_ID + "=? AND "
                + GrappboxContract.TimelineMessageEntry.TABLE_NAME + "." + GrappboxContract.TimelineMessageEntry.COLUMN_PARENT_ID + "=?";
                selectionArgs = new String[] {
                    String.valueOf(lpid),
                    String.valueOf(parent._grappboxId)
                };
                break;

            default:
                throw new IllegalArgumentException("Type doesn't exist");
        }

        CursorLoader cursorLoader = new CursorLoader(getActivity(), GrappboxContract.TimelineMessageEntry.CONTENT_URI, null, selection, selectionArgs, sortOrder);
        return cursorLoader;
    }

    @Override
    public void onLoadFinished(Loader<Cursor> loader, Cursor data) {
        Log.v(LOG_TAG, "cursor size : " + data.getCount());
    }

    @Override
    public void onLoaderReset(Loader<Cursor> loader) {

    }

    class AdapterObserver extends RecyclerView.AdapterDataObserver {
        @Override
        public void onChanged() {
            super.onChanged();
        }
    }
}
