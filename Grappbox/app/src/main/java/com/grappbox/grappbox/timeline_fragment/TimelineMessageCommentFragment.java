package com.grappbox.grappbox.timeline_fragment;

import android.database.Cursor;
import android.os.Bundle;
import android.support.annotation.Nullable;
import android.support.v4.app.Fragment;
import android.support.v4.app.LoaderManager;
import android.support.v4.content.CursorLoader;
import android.support.v4.content.Loader;
import android.support.v4.widget.SwipeRefreshLayout;
import android.support.v7.widget.LinearLayoutManager;
import android.support.v7.widget.RecyclerView;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.TextView;

import com.grappbox.grappbox.ProjectActivity;
import com.grappbox.grappbox.R;
import com.grappbox.grappbox.adapter.TimelineMessageCommentAdapter;
import com.grappbox.grappbox.data.GrappboxContract;
import com.grappbox.grappbox.model.TimelineModel;

import java.sql.Time;

public class TimelineMessageCommentFragment extends Fragment implements LoaderManager.LoaderCallbacks<Cursor>, SwipeRefreshLayout.OnRefreshListener {

    private static final String EXTRA_PARENT_ID = "parentId";

    private RecyclerView mRecycler;
    private TimelineMessageCommentAdapter mAdapter;
    private TextView    mTimelineMessage;

    public static final int TIMELINE_COMMENT = 0;
    public static final int TIMELINE_OFFSET = 30;

    public TimelineMessageCommentFragment(){
        // Required empty public constructor
    }

    @Nullable
    @Override
    public View onCreateView(LayoutInflater inflater, @Nullable ViewGroup container,
                             @Nullable Bundle savedInstanceState) {
        TimelineModel model = getActivity().getIntent().getParcelableExtra(TimelineMessageCommentActivity.EXTRA_TIMELINE_MODEL);
        View view = inflater.inflate(R.layout.timeline_message_comment, container, false);
        mRecycler = (RecyclerView) view.findViewById(R.id.scrollable_content);
        mAdapter = new TimelineMessageCommentAdapter(getActivity());
        mAdapter.setTimelineModel(model);
        mRecycler.setAdapter(mAdapter);
        mRecycler.setLayoutManager(new LinearLayoutManager(getActivity()));
        mTimelineMessage = (TextView) view.findViewById(R.id.message);
        mTimelineMessage.setText(model._message);

        return view;
    }

    @Override
    public void onRefresh() {

    }

    @Override
    public Loader<Cursor> onCreateLoader(int id, Bundle args) {
        String sortOrder = "datetime(" + GrappboxContract.TimelineMessageEntry.COLUMN_DATE_LAST_EDITED_AT_UTC + ") DESC LIMIT 30";
        String selection;
        String[] selectionArgs;
        long lpid = getActivity().getIntent().getLongExtra(ProjectActivity.EXTRA_PROJECT_ID, -1);
        long parentId = getActivity().getIntent().getLongExtra(EXTRA_PARENT_ID, -1);

        switch (id) {
            case TIMELINE_COMMENT:
                selection = GrappboxContract.TimelineEntry.TABLE_NAME + "." + GrappboxContract.TimelineEntry.COLUMN_LOCAL_PROJECT_ID + "=? AND "
                + GrappboxContract.TimelineMessageEntry.TABLE_NAME + "." + GrappboxContract.TimelineMessageEntry.COLUMN_PARENT_ID + "=?";
                selectionArgs = new String[] {
                    String.valueOf(lpid),
                    String.valueOf(parentId)
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

    }

    @Override
    public void onLoaderReset(Loader<Cursor> loader) {

    }
}
