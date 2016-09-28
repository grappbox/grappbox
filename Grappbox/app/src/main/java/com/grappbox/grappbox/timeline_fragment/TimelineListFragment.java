package com.grappbox.grappbox.timeline_fragment;

import android.database.Cursor;
import android.os.Bundle;
import android.os.Handler;
import android.support.annotation.Nullable;
import android.support.v4.app.Fragment;
import android.support.v4.app.LoaderManager;
import android.support.v4.content.CursorLoader;
import android.support.v4.content.Loader;
import android.support.v4.view.ViewPager;
import android.support.v4.widget.SwipeRefreshLayout;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.ListView;

import com.grappbox.grappbox.ProjectActivity;
import com.grappbox.grappbox.R;
import com.grappbox.grappbox.adapter.TimelineListAdapter;
import com.grappbox.grappbox.data.GrappboxContract;
import com.grappbox.grappbox.data.GrappboxContract.TimelineMessageEntry;
import com.grappbox.grappbox.receiver.RefreshReceiver;

public class TimelineListFragment extends Fragment implements LoaderManager.LoaderCallbacks<Cursor>, SwipeRefreshLayout.OnRefreshListener {

    public static final String ARG_LIST_TYPE = "com.grappbox.grappbox.timeline_fragment.ARG_LIST_TYPE";
    public static final String LOG_TAG = TimelineListFragment.class.getSimpleName();

    public static final int TIMELINE_TEAM = 0;
    public static final int TIMELINE_CLIENT = 1;

    private ListView    mTimelineList;
    private SwipeRefreshLayout  mRefresher;
    private RefreshReceiver mRefreshReceiver = null;
    private TimelineListAdapter mAdapter;

    public TimelineListFragment(){
        // Required empty public constructor
    }


    @Override
    public void onActivityCreated(@Nullable Bundle savedInstanceState) {
        super.onActivityCreated(savedInstanceState);
        if (savedInstanceState == null){
            getLoaderManager().initLoader(getArguments().getInt(ARG_LIST_TYPE), null, this);
        } else {
            getLoaderManager().restartLoader(getArguments().getInt(ARG_LIST_TYPE), null, this);
        }
    }

    @Override
    public View onCreateView(LayoutInflater inflater, ViewGroup container,
                             Bundle savedInstanceState) {
        View v = inflater.inflate(R.layout.fragment_timeline_list, container, false);

        mTimelineList = (ListView) v.findViewById(R.id.timelinelist);
        mRefresher = (SwipeRefreshLayout) v.findViewById(R.id.refresh);
        mAdapter = new TimelineListAdapter(getActivity(), null, 0);
        mTimelineList.setAdapter(mAdapter);
        mRefreshReceiver = new RefreshReceiver(new Handler(), mRefresher, getActivity());
        mRefresher.setOnRefreshListener(this);
        return v;
    }


    @Override
    public void onRefresh() {

    }

    @Override
    public Loader<Cursor> onCreateLoader(int id, Bundle args) {
        String sortOrder = "datetime(" + TimelineMessageEntry.COLUMN_DATE_LAST_EDITED_AT_UTC + ") DESC";
        String select = null;
        String[] selectionArgs = null;

        long lpid = getActivity().getIntent().getLongExtra(ProjectActivity.EXTRA_PROJECT_ID, -1);

        switch (id){
            case TIMELINE_TEAM:
                select = GrappboxContract.TimelineEntry.TABLE_NAME + "." + GrappboxContract.TimelineEntry.COLUMN_LOCAL_PROJECT_ID + "=? AND "
                + TimelineMessageEntry.COLUMN_PARENT_ID + " IS NULL";
                selectionArgs = new String[]{
                        String.valueOf(lpid)
                };
                break;

            case TIMELINE_CLIENT:
                select = GrappboxContract.TimelineEntry.TABLE_NAME + "." +  GrappboxContract.TimelineEntry.COLUMN_LOCAL_PROJECT_ID + "=? AND "
                        + TimelineMessageEntry.COLUMN_PARENT_ID + " IS NULL";
                selectionArgs = new String[]{
                        String.valueOf(lpid)
                };
                break;
        }
        return new CursorLoader(getActivity(), TimelineMessageEntry.CONTENT_URI, TimelineListAdapter.projection, select, selectionArgs, sortOrder);
    }

    @Override
    public void onLoadFinished(Loader<Cursor> loader, Cursor data) {

    }

    @Override
    public void onLoaderReset(Loader<Cursor> loader) {

    }
}
