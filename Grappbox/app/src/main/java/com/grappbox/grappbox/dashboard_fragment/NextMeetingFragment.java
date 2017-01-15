package com.grappbox.grappbox.dashboard_fragment;

import android.database.Cursor;
import android.os.Bundle;
import android.support.annotation.Nullable;
import android.support.v4.app.Fragment;
import android.support.v4.app.LoaderManager;
import android.support.v4.content.ContextCompat;
import android.support.v4.content.CursorLoader;
import android.support.v4.content.Loader;
import android.support.v4.widget.SwipeRefreshLayout;
import android.support.v7.widget.LinearLayoutManager;
import android.support.v7.widget.RecyclerView;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.ProgressBar;

import com.grappbox.grappbox.ProjectActivity;
import com.grappbox.grappbox.R;
import com.grappbox.grappbox.adapter.NextMeetingAdapter;
import com.grappbox.grappbox.data.GrappboxContract.NextMeetingEntry;
import com.grappbox.grappbox.item_decoration.HorizontalDivider;
import com.grappbox.grappbox.model.NextMeetingModel;

import java.util.ArrayList;
import java.util.List;

/**
 * Created by tan_f on 02/01/2017.
 */

public class NextMeetingFragment extends AbstractDashboard implements LoaderManager.LoaderCallbacks<Cursor>, SwipeRefreshLayout.OnRefreshListener  {

    public static final String LOG_TAG = NextMeetingFragment.class.getSimpleName();

    public static final int NEXT_MEETING = 0;

    private NextMeetingAdapter mAdapter = null;
    private RecyclerView mNextMeetingList = null;
    private ProgressBar mLoader;
    private SwipeRefreshLayout mRefresher;

    public static final String[] projectionNextMeeting = {
            NextMeetingEntry.TABLE_NAME + "." + NextMeetingEntry._ID,
            NextMeetingEntry.TABLE_NAME + "." + NextMeetingEntry.COLUMN_LOCAL_PROJECT_ID,
            NextMeetingEntry.TABLE_NAME + "." + NextMeetingEntry.COLUMN_TITLE,
            NextMeetingEntry.TABLE_NAME + "." + NextMeetingEntry.COLUMN_DESCRIPTION,
            NextMeetingEntry.TABLE_NAME + "." + NextMeetingEntry.COLUMN_BEGIN_DATE,
            NextMeetingEntry.TABLE_NAME + "." + NextMeetingEntry.COLUMN_END_DATE
    };

    @Nullable
    @Override
    public View onCreateView(LayoutInflater inflater, @Nullable ViewGroup container, @Nullable Bundle savedInstanceState) {
        View v = inflater.inflate(R.layout.fragment_next_meeting, container, false);
        mNextMeetingList = (RecyclerView) v.findViewById(R.id.next_meeting_list);
        mAdapter = new NextMeetingAdapter(getActivity());
        mNextMeetingList.setAdapter(mAdapter);
        mNextMeetingList.addItemDecoration(new HorizontalDivider(ContextCompat.getColor(getActivity(), R.color.GrappGrayMedium)));
        mNextMeetingList.setLayoutManager(new LinearLayoutManager(getActivity()));

        mLoader = (ProgressBar) v.findViewById(R.id.loader);
        mRefresher = (SwipeRefreshLayout) v.findViewById(R.id.refresh);
        mRefresher.setOnRefreshListener(this);
        mAdapter.registerAdapterDataObserver(new AdapterObserver());

        if (savedInstanceState != null)
            mRefresher.setVisibility(View.VISIBLE);
        getLoaderManager().initLoader(NEXT_MEETING, null, this);
        return v;
    }

    @Override
    public Loader<Cursor> onCreateLoader(int id, Bundle args) {
        long lpid = getActivity().getIntent().getLongExtra(ProjectActivity.EXTRA_PROJECT_ID, -1);
        String selection;
        String[] selectionArgs;

        String sortOrder = "date(" + NextMeetingEntry.COLUMN_BEGIN_DATE + ") ASC";
        switch (id) {
            case NEXT_MEETING:
                selection = NextMeetingEntry.TABLE_NAME + "." + NextMeetingEntry.COLUMN_LOCAL_PROJECT_ID + "=?";
                selectionArgs = new String[]{ String.valueOf(lpid) };
                break;

            default:
                throw new IllegalArgumentException("Type doesn't exist");
        }
        return new CursorLoader(getActivity(), NextMeetingEntry.CONTENT_URI, projectionNextMeeting, selection, selectionArgs, sortOrder);
    }

    @Override
    public void onLoadFinished(Loader<Cursor> loader, Cursor data) {
        mAdapter.clear();
        if (data == null  || !data.moveToFirst())
            return;
        List<NextMeetingModel> models = new ArrayList<>();
        do {
            models.add(new NextMeetingModel(data));
        } while (data.moveToNext());
        mAdapter.addAll(models);
    }

    @Override
    public void onLoaderReset(Loader<Cursor> loader) {

    }

    class AdapterObserver extends RecyclerView.AdapterDataObserver {

        @Override
        public void onChanged() {
            super.onChanged();
            mLoader.setVisibility(View.GONE);
            mRefresher.setVisibility(View.VISIBLE);
        }
    }

    @Override
    public void onRefresh() {

    }
}
