package com.grappbox.grappbox.project_fragments;


import android.accounts.AccountManager;
import android.content.Intent;
import android.database.Cursor;
import android.os.Bundle;
import android.support.annotation.Nullable;
import android.support.design.widget.FloatingActionButton;
import android.support.v4.app.Fragment;
import android.support.v4.app.LoaderManager;
import android.support.v4.content.ContextCompat;
import android.support.v4.content.CursorLoader;
import android.support.v4.content.Loader;
import android.support.v7.widget.LinearLayoutManager;
import android.support.v7.widget.RecyclerView;
import android.util.Log;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.CalendarView;

import com.grappbox.grappbox.R;
import com.grappbox.grappbox.adapter.CalendarEventAdapter;
import com.grappbox.grappbox.calendar_fragment.NewEventActivity;
import com.grappbox.grappbox.data.GrappboxContract;
import com.grappbox.grappbox.item_decoration.HorizontalDivider;
import com.grappbox.grappbox.model.CalendarEventModel;
import com.grappbox.grappbox.singleton.Session;
import com.grappbox.grappbox.sync.GrappboxJustInTimeService;

import java.text.SimpleDateFormat;
import java.util.ArrayList;
import java.util.Calendar;
import java.util.List;

public class CalendarFragment extends Fragment implements LoaderManager.LoaderCallbacks<Cursor> {

    private static final String LOG_TAG = CalendarFragment.class.getSimpleName();

    private CalendarView mCalendarView;
    private RecyclerView mRecyclerView;
    private CalendarEventAdapter mAdapter;
    private FloatingActionButton    mAddEvent;
    private LinearLayoutManager mLinearLayoutManager;

    private final int CALENDAR_TYPE = 0;
    private Calendar mCalendar;
    private SimpleDateFormat mRequestFormat = new SimpleDateFormat("yyyy-MM-dd");

    public CalendarFragment() {
        // Required empty public constructor
    }

    @Override
    public void onActivityCreated(@Nullable Bundle savedInstanceState) {
        super.onActivityCreated(savedInstanceState);
        getLoaderManager().initLoader(CALENDAR_TYPE, null, this);
    }

    @Override
    public View onCreateView(LayoutInflater inflater, ViewGroup container,
                             Bundle savedInstanceState) {
        View v = inflater.inflate(R.layout.fragment_calendar, container, false);

        mRecyclerView = (RecyclerView) v.findViewById(R.id.agendalist);
        mAdapter = new CalendarEventAdapter(getActivity());
        mRecyclerView.setAdapter(mAdapter);
        mLinearLayoutManager = new LinearLayoutManager(getActivity());
        mRecyclerView.addItemDecoration(new HorizontalDivider(ContextCompat.getColor(getActivity(), R.color.GrappBlue)));
        mRecyclerView.setLayoutManager(mLinearLayoutManager);
        mCalendarView = (CalendarView)v.findViewById(R.id.calendarview);
        mCalendar = Calendar.getInstance();
        mCalendar.setTimeInMillis(mCalendarView.getDate());
        mAddEvent = (FloatingActionButton) v.findViewById(R.id.fab);
        mAddEvent.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                Intent newEvent = new Intent(getContext(), NewEventActivity.class);
                getContext().startActivity(newEvent);
            }
        });

        return v;
    }

    @Override
    public Loader<Cursor> onCreateLoader(int id, Bundle args) {
        Calendar firstDay = Calendar.getInstance();
        firstDay.setTimeInMillis(mCalendarView.getDate());
        firstDay.set(Calendar.DAY_OF_MONTH, 1);
        String sort = null;
        AccountManager am = AccountManager.get(getActivity());
        long uid = Long.parseLong(am.getUserData(Session.getInstance(getActivity()).getCurrentAccount(), GrappboxJustInTimeService.EXTRA_USER_ID));

        final String[] projection = {GrappboxContract.EventEntry.TABLE_NAME + "." + GrappboxContract.EventEntry._ID,
                GrappboxContract.EventEntry.TABLE_NAME + "." + GrappboxContract.EventEntry.COLUMN_GRAPPBOX_ID,
                GrappboxContract.EventEntry.TABLE_NAME + "." + GrappboxContract.EventEntry.COLUMN_LOCAL_CREATOR_ID,
                GrappboxContract.EventEntry.TABLE_NAME + "." + GrappboxContract.EventEntry.COLUMN_EVENT_TITLE,
                GrappboxContract.EventEntry.TABLE_NAME + "." + GrappboxContract.EventEntry.COLUMN_EVENT_DESCRIPTION,
                GrappboxContract.EventEntry.TABLE_NAME + "." + GrappboxContract.EventEntry.COLUMN_LOCAL_PROJECT_ID,
                GrappboxContract.EventEntry.TABLE_NAME + "." + GrappboxContract.EventEntry.COLUMN_DATE_BEGIN_UTC,
                GrappboxContract.EventEntry.TABLE_NAME + "." + GrappboxContract.EventEntry.COLUMN_DATE_END_UTC};
        final String selection = GrappboxContract.EventEntry.TABLE_NAME + "." + GrappboxContract.EventEntry.COLUMN_LOCAL_CREATOR_ID + "=?";
        final String[] arguments = {String.valueOf(uid)};

        return new CursorLoader(getContext(), GrappboxContract.EventEntry.CONTENT_URI, projection, selection, arguments, sort);
    }

    @Override
    public void onLoaderReset(Loader<Cursor> loader) {

    }

    @Override
    public void onLoadFinished(Loader<Cursor> loader, Cursor data) {
        if (data == null || !data.moveToFirst())
            return;
        List<CalendarEventModel> models = new ArrayList<>();
        do {
            models.add(new CalendarEventModel(data));
        } while (data.moveToNext());
        mAdapter.add(models);
    }
}
