package com.grappbox.grappbox.project_fragments;


import android.accounts.AccountManager;
import android.content.Intent;
import android.database.Cursor;
import android.os.AsyncTask;
import android.os.Bundle;
import android.support.annotation.NonNull;
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
import android.view.MotionEvent;
import android.view.View;
import android.view.ViewGroup;
import android.widget.CalendarView;

import com.grappbox.grappbox.R;
import com.grappbox.grappbox.Utils;
import com.grappbox.grappbox.adapter.CalendarEventAdapter;
import com.grappbox.grappbox.calendar_fragment.NewEventActivity;
import com.grappbox.grappbox.data.GrappboxContract;
import com.grappbox.grappbox.item_decoration.HorizontalDivider;
import com.grappbox.grappbox.model.CalendarEventModel;
import com.grappbox.grappbox.model.UserModel;
import com.grappbox.grappbox.singleton.Session;
import com.grappbox.grappbox.sync.GrappboxJustInTimeService;

import java.text.ParseException;
import java.text.SimpleDateFormat;
import java.util.ArrayList;
import java.util.Calendar;
import java.util.Collection;
import java.util.List;

public class CalendarFragment extends Fragment implements LoaderManager.LoaderCallbacks<Cursor> {

    private static final String     LOG_TAG = CalendarFragment.class.getSimpleName();

    private CalendarView            mCalendarView;
    private RecyclerView            mRecyclerView;
    private CalendarEventAdapter    mAdapter;
    private FloatingActionButton    mAddEvent;
    private LinearLayoutManager     mLinearLayoutManager;

    private final int CALENDAR_TYPE = 0;
    private CalendarFragment    mActivity = this;
    private Calendar            mCalendar;
    private SimpleDateFormat    mRequestFormat = new SimpleDateFormat("yyyy-MM-dd HH:mm:ss");

    private float       mInitX;
    private float       mFinalX;
    static final int    MIN_DISTANCE = 150;

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
        mCalendarView = (CalendarView)v.findViewById(R.id.calendarview);
        mCalendar = Calendar.getInstance();
        mCalendar.setTimeInMillis(mCalendarView.getDate());
        mCalendarView.setOnDateChangeListener(new CalendarView.OnDateChangeListener() {
            @Override
            public void onSelectedDayChange(@NonNull CalendarView view, int year, int month, int dayOfMonth) {
                mCalendar.set(Calendar.YEAR, year);
                mCalendar.set(Calendar.MONTH, month);
                mCalendar.set(Calendar.DAY_OF_MONTH, dayOfMonth);
                getLoaderManager().restartLoader(CALENDAR_TYPE, null, mActivity);
            }
        });
        mAddEvent = (FloatingActionButton) v.findViewById(R.id.fab);
        mAddEvent.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                Intent newEvent = new Intent(getContext(), NewEventActivity.class);
                newEvent.setAction(NewEventActivity.ACTION_NEW);
                getContext().startActivity(newEvent);
            }
        });
        mAdapter = new CalendarEventAdapter(getContext());
        initRecyclerView();
        return v;
    }

    private void initRecyclerView() {
        mRecyclerView.setAdapter(mAdapter);
        mLinearLayoutManager = new LinearLayoutManager(getActivity());
        mRecyclerView.addItemDecoration(new HorizontalDivider(ContextCompat.getColor(getActivity(), R.color.GrappBlue)));
        mRecyclerView.setLayoutManager(mLinearLayoutManager);
        mRecyclerView.setOnTouchListener(new View.OnTouchListener() {
            @Override
            public boolean onTouch(View v, MotionEvent event) {
                switch (event.getAction()){
                    case MotionEvent.ACTION_DOWN:
                        mInitX = event.getX();
                        return true;

                    case MotionEvent.ACTION_UP:
                        mFinalX = event.getX();
                        float deltaX = mFinalX - mInitX;
                        if (Math.abs(deltaX) > MIN_DISTANCE)
                        {
                            if (deltaX > 0){
                                mCalendar.add(Calendar.DAY_OF_MONTH, -1);
                                mCalendarView.setDate(mCalendar.getTimeInMillis());
                            } else {
                                mCalendar.add(Calendar.DAY_OF_MONTH, 1);
                                mCalendarView.setDate(mCalendar.getTimeInMillis());
                            }
                        }
                        getLoaderManager().restartLoader(CALENDAR_TYPE, null, mActivity);
                        return true;
                }
                return false;
            }

        });
    }

    @Override
    public Loader<Cursor> onCreateLoader(int id, Bundle args) {
        try {
            Calendar date = Calendar.getInstance();
            date.setTimeInMillis(mCalendar.getTimeInMillis());
            String sort = "date(" + GrappboxContract.EventEntry.TABLE_NAME + "." + GrappboxContract.EventEntry.COLUMN_DATE_BEGIN_UTC  + ") ASC";
            AccountManager am = AccountManager.get(getActivity());
            long uid = Long.parseLong(am.getUserData(Session.getInstance(getActivity()).getCurrentAccount(), GrappboxJustInTimeService.EXTRA_USER_ID));
            final String[] projection = {GrappboxContract.EventEntry.TABLE_NAME + "." + GrappboxContract.EventEntry._ID,
                    GrappboxContract.EventEntry.TABLE_NAME + "." + GrappboxContract.EventEntry.COLUMN_GRAPPBOX_ID,
                    GrappboxContract.EventEntry.TABLE_NAME + "." + GrappboxContract.EventEntry.COLUMN_LOCAL_CREATOR_ID,
                    GrappboxContract.EventEntry.TABLE_NAME + "." + GrappboxContract.EventEntry.COLUMN_EVENT_TITLE,
                    GrappboxContract.EventEntry.TABLE_NAME + "." + GrappboxContract.EventEntry.COLUMN_EVENT_DESCRIPTION,
                    GrappboxContract.EventEntry.TABLE_NAME + "." + GrappboxContract.EventEntry.COLUMN_LOCAL_PROJECT_ID,
                    GrappboxContract.EventEntry.TABLE_NAME + "." + GrappboxContract.EventEntry.COLUMN_DATE_BEGIN_UTC,
                    GrappboxContract.EventEntry.TABLE_NAME + "." + GrappboxContract.EventEntry.COLUMN_DATE_END_UTC
            };
            final String selection = GrappboxContract.EventEntry.TABLE_NAME + "." + GrappboxContract.EventEntry.COLUMN_LOCAL_CREATOR_ID + "=? AND " +
                    "date('" + Utils.Date.getDateFromGrappboxAPIToUTC(mRequestFormat.format(date.getTime())) + "') BETWEEN date(" + GrappboxContract.EventEntry.TABLE_NAME + "." + GrappboxContract.EventEntry.COLUMN_DATE_BEGIN_UTC  + ") AND date(" + GrappboxContract.EventEntry.TABLE_NAME + "." + GrappboxContract.EventEntry.COLUMN_DATE_END_UTC + ")";
            final String[] arguments = {String.valueOf(uid)};
            return new CursorLoader(getContext(), GrappboxContract.EventEntry.CONTENT_URI, projection, selection, arguments, sort);
        } catch (ParseException e) {
            e.printStackTrace();
            return null;
        }
    }

    @Override
    public void onLoaderReset(Loader<Cursor> loader) {
        loader.forceLoad();
    }

    @Override
    public void onLoadFinished(Loader<Cursor> loader, Cursor data) {
        mAdapter.clear();
        if (data == null || !data.moveToFirst())
            return;
        List<CalendarEventModel> models = new ArrayList<>();
        do {
            models.add(new CalendarEventModel(data));
        } while (data.moveToNext());
        AdditionnalDataLoader task = new AdditionnalDataLoader();
        task.execute(models);
    }

    public class AdditionnalDataLoader extends AsyncTask<Collection<CalendarEventModel>, Void, Collection<CalendarEventModel>> {

        @Override
        protected void onPostExecute(Collection<CalendarEventModel> data) {
            super.onPostExecute(data);
            mAdapter.add(data);
        }

        @Override
        protected Collection<CalendarEventModel> doInBackground(Collection<CalendarEventModel>... params) {
            if (params == null || params.length < 1)
                throw new IllegalArgumentException();

            final String[] projectionParticipant = {
                    GrappboxContract.UserEntry.TABLE_NAME + "." + GrappboxContract.UserEntry._ID,
                    GrappboxContract.UserEntry.TABLE_NAME + "." + GrappboxContract.UserEntry.COLUMN_FIRSTNAME,
                    GrappboxContract.UserEntry.TABLE_NAME + "." + GrappboxContract.UserEntry.COLUMN_LASTNAME,
                    GrappboxContract.UserEntry.TABLE_NAME + "." + GrappboxContract.UserEntry.COLUMN_DATE_BIRTHDAY_UTC,
                    GrappboxContract.UserEntry.TABLE_NAME + "." + GrappboxContract.UserEntry.COLUMN_URI_AVATAR,
                    GrappboxContract.UserEntry.TABLE_NAME + "." + GrappboxContract.UserEntry.COLUMN_CONTACT_EMAIL
            };
            String selectionParticipant = GrappboxContract.EventParticipantEntry.COLUMN_LOCAL_EVENT_ID + "=?";
            for (CalendarEventModel model : params[0]) {
                String[] args = { String.valueOf(model._id) };
                Cursor resultParticipant = getActivity().getContentResolver().query(GrappboxContract.EventParticipantEntry.CONTENT_URI, projectionParticipant, selectionParticipant, args, null);
                List<UserModel> participant = new ArrayList<>();
                Log.v(LOG_TAG, "event ID : " + model._id);
                if (resultParticipant != null) {
                    if (resultParticipant.moveToFirst()) {
                        do {
                            UserModel parti = new UserModel(resultParticipant);
                            participant.add(parti);
                            Log.v(LOG_TAG, "user : " + parti);
                        }while (resultParticipant.moveToNext());
                    }
                    resultParticipant.close();
                }
                model.setParticipant(participant);
            }
            return params[0];
        }
    }
}
