package com.grappbox.grappbox.calendar_fragment;

import android.database.Cursor;
import android.os.Bundle;
import android.support.annotation.Nullable;
import android.support.v4.app.Fragment;
import android.support.v4.app.LoaderManager;
import android.support.v4.content.Loader;
import android.support.v7.widget.RecyclerView;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;

import com.grappbox.grappbox.R;
import com.grappbox.grappbox.model.CalendarEventModel;

/**
 * Created by tan_f on 16/11/2016.
 */

public class EventDetailsFragment extends Fragment implements LoaderManager.LoaderCallbacks<Cursor> {

    private RecyclerView mRecycler;
    private CalendarEventModel mModel;

    @Nullable
    @Override
    public View onCreateView(LayoutInflater inflater, @Nullable ViewGroup container, @Nullable Bundle savedInstanceState) {
        super.onCreateView(inflater, container, savedInstanceState);
        mModel = getActivity().getIntent().getParcelableExtra(EventDetailsActivity.EXTRA_CALENDAR_EVENT_MODEL);
        View v = inflater.inflate(R.layout.fragment_calendar_event_details, container, false);
        mRecycler = (RecyclerView)v.findViewById(R.id.scrollable_content);

        return v;

    }

    @Override
    public Loader<Cursor> onCreateLoader(int id, Bundle args) {
        return null;
    }

    @Override
    public void onLoadFinished(Loader<Cursor> loader, Cursor data) {

    }

    @Override
    public void onLoaderReset(Loader<Cursor> loader) {

    }
}
