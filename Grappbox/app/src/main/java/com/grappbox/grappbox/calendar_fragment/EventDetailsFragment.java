package com.grappbox.grappbox.calendar_fragment;

import android.database.Cursor;
import android.os.Bundle;
import android.support.annotation.Nullable;
import android.support.v4.app.Fragment;
import android.support.v4.app.LoaderManager;
import android.support.v4.content.CursorLoader;
import android.support.v4.content.Loader;
import android.support.v7.widget.LinearLayoutManager;
import android.support.v7.widget.RecyclerView;
import android.util.Log;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.TextView;

import com.grappbox.grappbox.R;
import com.grappbox.grappbox.adapter.CalendarDetailAdapter;
import com.grappbox.grappbox.data.GrappboxContract;
import com.grappbox.grappbox.model.CalendarEventModel;
import com.grappbox.grappbox.receiver.CalendarEventReceiver;

/**
 * Created by tan_f on 16/11/2016.
 */

public class EventDetailsFragment extends Fragment implements LoaderManager.LoaderCallbacks<Cursor>, CalendarEventReceiver.Callback {

    private static final String LOG_TAG = EventDetailsFragment.class.getSimpleName();

    private RecyclerView mRecycler;
    private CalendarDetailAdapter mAdapter;
    private CalendarEventModel mModel;
    private TextView mDescription;
    private TextView mEventBegin;
    private TextView mEventEnd;
    private TextView mProjectName;

    @Override
    public void onActivityCreated(@Nullable Bundle savedInstanceState) {
        super.onActivityCreated(savedInstanceState);
        getActivity().getSupportLoaderManager().initLoader(0, null, this);
    }

    @Nullable
    @Override
    public View onCreateView(LayoutInflater inflater, @Nullable ViewGroup container, @Nullable Bundle savedInstanceState) {
        super.onCreateView(inflater, container, savedInstanceState);
        mModel = getActivity().getIntent().getParcelableExtra(EventDetailsActivity.EXTRA_CALENDAR_EVENT_MODEL);
        View v = inflater.inflate(R.layout.fragment_calendar_event_details, container, false);
        mDescription = (TextView) v.findViewById(R.id.description);
        mDescription.setText(mModel._description);
        mEventBegin = (TextView) v.findViewById(R.id.event_begin);
        mEventBegin.setText(mModel._beginDate);
        mEventEnd = (TextView) v.findViewById(R.id.event_end);
        mEventEnd.setText(mModel._endDate);
        mProjectName = (TextView) v.findViewById(R.id.event_project_name);
        mRecycler = (RecyclerView)v.findViewById(R.id.scrollable_content);
        mAdapter = new CalendarDetailAdapter(getActivity());
        mAdapter.setEventModel(mModel);
        mRecycler.setAdapter(mAdapter);
        mRecycler.setLayoutManager(new LinearLayoutManager(getActivity()));
        if (getActivity() instanceof EventDetailsActivity) {
            ((EventDetailsActivity)getActivity()).registerActivityActionCallback(this);
        }

        return v;

    }

    @Override
    public void onDataReceived(CalendarEventModel model) {
        getActivity().finish();
    }

    @Override
    public Loader<Cursor> onCreateLoader(int id, Bundle args) {
        if (mModel != null && mModel._projectId != -1) {
            final String[] participantProjection = {
                    GrappboxContract.ProjectEntry.TABLE_NAME + "." + GrappboxContract.ProjectEntry.COLUMN_NAME
            };
            String participantSelection = GrappboxContract.ProjectEntry._ID + "=?";
            return new CursorLoader(getActivity(), GrappboxContract.ProjectEntry.CONTENT_URI, participantProjection, participantSelection, new String[]{String.valueOf(mModel._projectId)}, null);
        }
        return null;
    }

    @Override
    public void onLoadFinished(Loader<Cursor> loader, Cursor data) {
        if (data == null || !data.moveToFirst()) {
            mProjectName.setText(R.string.event_no_project);
            return;
        }
        mProjectName.setText(data.getString(0));
    }

    @Override
    public void onLoaderReset(Loader<Cursor> loader) {

    }
}
