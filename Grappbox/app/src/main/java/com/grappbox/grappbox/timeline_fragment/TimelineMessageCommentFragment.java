package com.grappbox.grappbox.timeline_fragment;

import android.database.Cursor;
import android.os.AsyncTask;
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
import com.grappbox.grappbox.model.TimelineMessageCommentModel;
import com.grappbox.grappbox.model.TimelineModel;
import com.grappbox.grappbox.receiver.RefreshReceiver;

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

    public static final int TIMELINE_LOADER = 0;
    public static final int TIMELINE_COMMENT = 0;

    public static final String[] projectionMessage = {
            GrappboxContract.TimelineEntry.TABLE_NAME + "." + GrappboxContract.TimelineEntry._ID,
            GrappboxContract.TimelineEntry.TABLE_NAME + "." + GrappboxContract.TimelineEntry.COLUMN_TYPE_ID,
            GrappboxContract.TimelineCommentEntry.TABLE_NAME + "." + GrappboxContract.TimelineCommentEntry._ID,
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
        mRecycler = (RecyclerView) view.findViewById(R.id.scrollable_content);
        mAdapter = new TimelineMessageCommentAdapter(getActivity());
        mAdapter.setTimelineModel(parent);
        mRecycler.setAdapter(mAdapter);
        mLinearLayoutManager = new LinearLayoutManager(getContext());
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
        this.getLoaderManager().initLoader(TIMELINE_LOADER, null, this);
        return view;
    }

    @Override
    public void onRefresh() {

    }



    @Override
    public Loader<Cursor> onCreateLoader(int id, Bundle args) {
        Log.v(LOG_TAG, "create loader");
        String sortOrder = "datetime(" + GrappboxContract.TimelineCommentEntry.COLUMN_DATE_LAST_EDITED_AT_UTC +
                ") DESC LIMIT " + mAdapter.getSize() + ", 10";
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
        return new CursorLoader(getActivity(), GrappboxContract.TimelineMessageEntry.CONTENT_URI, projectionMessage, selection, selectionArgs, sortOrder);
    }

    class StringDateComparator implements Comparator<TimelineMessageCommentModel>
    {

        SimpleDateFormat dateFormat = new SimpleDateFormat("dd/MM/yyyy");

        @Override
        public int compare(TimelineMessageCommentModel o1, TimelineMessageCommentModel o2) {

            try {
                return dateFormat.parse(o2._lastupdate).compareTo(dateFormat.parse(o1._lastupdate));
            } catch (ParseException e) {
                e.printStackTrace();
            }
            return -1;
        }
    }

    @Override
    public void onLoadFinished(Loader<Cursor> loader, Cursor data) {
        if (loader.getId() == TIMELINE_COMMENT) {
            if (!data.moveToFirst())
                return;
            List<TimelineMessageCommentModel> models = new ArrayList<>();
            do {
                models.add(new TimelineMessageCommentModel(getActivity(), data));
            } while (data.moveToNext());
            Collections.sort(models, new StringDateComparator());
            AdditionalDataLoader task = new AdditionalDataLoader();
            task.execute(models);
        }
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

    private class AdditionalDataLoader extends AsyncTask<Collection<TimelineMessageCommentModel>, Void, Collection<TimelineMessageCommentModel>> {

        @Override
        protected void onPostExecute(Collection<TimelineMessageCommentModel> timelineModels) {
            super.onPostExecute(timelineModels);
            mAdapter.clear();
            mAdapter.add(timelineModels);
            mLinearLayoutManager.scrollToPosition(mAdapter.getSize());
        }

        @Override
        protected Collection<TimelineMessageCommentModel> doInBackground(Collection<TimelineMessageCommentModel>... params) {
            if (params == null || params.length < 1)
                throw new IllegalArgumentException();
            return params[0];
        }
    }

}
