package com.grappbox.grappbox.calendar_fragment;

import android.database.Cursor;
import android.os.Bundle;
import android.support.annotation.Nullable;
import android.support.v4.app.Fragment;
import android.support.v4.app.LoaderManager;
import android.support.v4.content.CursorLoader;
import android.support.v4.content.Loader;
import android.support.v7.widget.RecyclerView;
import android.util.Log;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.TextView;

import com.grappbox.grappbox.R;
import com.grappbox.grappbox.data.GrappboxContract;
import com.grappbox.grappbox.model.CalendarEventModel;

/**
 * Created by tan_f on 16/11/2016.
 */

public class EventDetailsFragment extends Fragment implements LoaderManager.LoaderCallbacks<Cursor> {

    private static final String LOG_TAG = EventDetailsFragment.class.getSimpleName();

    private RecyclerView mRecycler;
    private CalendarEventModel mModel;
    private TextView mDescription;
    private TextView mEventBegin;
    private TextView mEventEnd;

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
        mRecycler = (RecyclerView)v.findViewById(R.id.scrollable_content);

        return v;

    }

    @Override
    public Loader<Cursor> onCreateLoader(int id, Bundle args) {
        final String[] participantProjection = {
                GrappboxContract.EventParticipantEntry.TABLE_NAME + "." + GrappboxContract.EventParticipantEntry._ID,
                GrappboxContract.EventParticipantEntry.TABLE_NAME + "." + GrappboxContract.EventParticipantEntry.COLUMN_LOCAL_USER_ID,
        };
        String participantSelection = GrappboxContract.EventParticipantEntry.COLUMN_LOCAL_EVENT_ID + "=?";
        return new CursorLoader(getActivity(), GrappboxContract.EventParticipantEntry.CONTENT_URI, participantProjection, participantSelection, new String[]{ String.valueOf(mModel._id) }, null);
    }

    @Override
    public void onLoadFinished(Loader<Cursor> loader, Cursor data) {
        if (data == null || !data.moveToFirst())
            return;
        do {
            Log.v(LOG_TAG, "local id : " + data.getString(0));
        } while (data.moveToNext());
    }

    @Override
    public void onLoaderReset(Loader<Cursor> loader) {

    }
}
