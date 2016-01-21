package com.grappbox.grappbox.grappbox.Calendar;

import android.app.DialogFragment;
import android.content.ContentValues;
import android.os.AsyncTask;
import android.os.Bundle;
import android.support.design.widget.FloatingActionButton;
import android.support.v4.app.Fragment;
import android.support.v4.content.ContextCompat;
import android.util.Log;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;

import com.github.tibolte.agendacalendarview.AgendaCalendarView;
import com.github.tibolte.agendacalendarview.CalendarPickerController;
import com.github.tibolte.agendacalendarview.models.BaseCalendarEvent;
import com.github.tibolte.agendacalendarview.models.CalendarEvent;
import com.github.tibolte.agendacalendarview.models.DayItem;
import com.grappbox.grappbox.grappbox.Model.APIConnectAdapter;
import com.grappbox.grappbox.grappbox.Model.SessionAdapter;
import com.grappbox.grappbox.grappbox.R;

import org.json.JSONException;

import java.io.IOException;
import java.text.ParseException;
import java.text.SimpleDateFormat;
import java.util.ArrayList;
import java.util.Calendar;
import java.util.List;
import java.util.Locale;

import butterknife.ButterKnife;

/**
 * Created by tan_f on 21/01/2016.
 */
public class AgendaFragment extends Fragment implements CalendarPickerController {

    private static final String LOG_TAG = AgendaFragment.class.getSimpleName();

    private View _rootView;
    private AgendaCalendarView _AgendaCalendarView;
    private Calendar _minDate = Calendar.getInstance();
    private Calendar _maxDate = Calendar.getInstance();

    public View onCreateView(LayoutInflater inflater, ViewGroup container,
                             Bundle savedInstanceState) {
        _rootView = inflater.inflate(R.layout.fragment_agenda_calendar, container, false);

        ButterKnife.bind(this.getActivity());


        _minDate.add(Calendar.YEAR, -3);
        _minDate.set(Calendar.DAY_OF_MONTH, 1);
        _maxDate.add(Calendar.YEAR, 3);

        //List<CalendarEvent> eventList = new ArrayList<>();

        _AgendaCalendarView = (AgendaCalendarView)_rootView.findViewById(R.id.agenda_calendar_view);
/*        _AgendaCalendarView.init(eventList, _minDate, _maxDate, Locale.getDefault(), this);
        _AgendaCalendarView.addEventRenderer(new DrawableEventRenderer());*/

        FloatingActionButton fab = (FloatingActionButton) _rootView.findViewById(R.id.add_event_float_button);
        fab.setOnClickListener((View v)-> {
            DialogFragment addEvent = new AddEventDialogFragment();
            addEvent.show(getActivity().getFragmentManager(), "AddEvent");
//                Snackbar.make(view, "Replace with your own action", Snackbar.LENGTH_LONG)
//                        .setAction("Action", null).show();
        });

        SimpleDateFormat format = new SimpleDateFormat("yyyy-MM-dd");

        Calendar cal = Calendar.getInstance();
        cal.set(cal.get(Calendar.YEAR), cal.get(Calendar.MONTH), 1);

        String currentDate = format.format(cal.getTime());
        Log.v("Date = ", currentDate);
        APIRequestCalendarEvent api = new APIRequestCalendarEvent();
        api.execute(currentDate);
        return _rootView;
    }

    @Override
    public void onDaySelected(DayItem dayItem) {
        Log.d(LOG_TAG, String.format("Selected day: %s", dayItem));
    }

    @Override
    public void onEventSelected(CalendarEvent event) {
        if (!event.getTitle().contains("No events"))
        {
            Fragment eventDetail = new EventDetailFragment();
            android.support.v4.app.FragmentManager fragManager = getFragmentManager();
            android.support.v4.app.FragmentTransaction ft = fragManager.beginTransaction();
            Bundle args = new Bundle();
            args.putInt("idEvent", (int)event.getId());
            eventDetail.setArguments(args);
            ft.replace(R.id.content_frame, eventDetail).commit();
        }
    }

    private void fillListEvent(List<ContentValues> eventList) throws ParseException {

        SimpleDateFormat format = new SimpleDateFormat("yyyy-MM-dd HH:mm:ss");
        SimpleDateFormat hourFormat = new SimpleDateFormat("HH:mm");
        List<CalendarEvent> calendarEventList = new ArrayList<>();
        for (ContentValues event : eventList) {

            Calendar beginDate = Calendar.getInstance();
            beginDate.setTime(format.parse(event.get("beginDate").toString()));
            Calendar endDate = Calendar.getInstance();
            endDate.setTime(format.parse(event.get("endDate").toString()));

            BaseCalendarEvent calendarEvent;
            if (beginDate.get(beginDate.YEAR) == endDate.get(endDate.YEAR) &&
                    beginDate.get(beginDate.MONTH) == endDate.get(endDate.MONTH) &&
                    beginDate.get(beginDate.DAY_OF_MONTH) == endDate.get(endDate.DAY_OF_MONTH)) {
                String title = event.get("title").toString() + " " + hourFormat.format(beginDate.getTime()) + " - " + hourFormat.format(endDate.getTime());
                calendarEvent = new BaseCalendarEvent(Long.parseLong(event.get("id").toString()), ContextCompat.getColor(this.getContext(), R.color.Brown), title,
                        "", "", beginDate.getTimeInMillis(), endDate.getTimeInMillis(), 0, "");
            } else{
                calendarEvent = new BaseCalendarEvent(Long.parseLong(event.get("id").toString()), ContextCompat.getColor(this.getContext(), R.color.Brown), event.get("title").toString(),
                        "", "", beginDate.getTimeInMillis(), endDate.getTimeInMillis(), 0, "");
            }

            calendarEventList.add(calendarEvent);
        }
        _AgendaCalendarView.init(calendarEventList, _minDate, _maxDate, Locale.getDefault(), this);
        _AgendaCalendarView.addEventRenderer(new DrawableEventRenderer());
    }

    private void mockList(List<CalendarEvent> eventList) {
        Calendar startTime1 = Calendar.getInstance();
        startTime1.add(Calendar.DAY_OF_YEAR, 1);
        Calendar endTime1 = Calendar.getInstance();
        endTime1.add(Calendar.DAY_OF_YEAR, 1);
        BaseCalendarEvent event1 = new BaseCalendarEvent("Meeting Client 16h00 - 19h00", "A wonderful journey!", "Bordeaux",
                ContextCompat.getColor(this.getContext(), R.color.Brown), startTime1, endTime1, false);
        eventList.add(event1);

        Calendar startTime2 = Calendar.getInstance();
        startTime2.add(Calendar.DAY_OF_YEAR, 1);
        Calendar endTime2 = Calendar.getInstance();
        endTime2.add(Calendar.DAY_OF_YEAR, 3);
        BaseCalendarEvent event2 = new BaseCalendarEvent("Visit to Paris", "A beautiful small town", "Paris",
                ContextCompat.getColor(this.getContext(), R.color.Light_Green), startTime2, endTime2, true);
        eventList.add(event2);

        Calendar startTime3 = Calendar.getInstance();
        Calendar endTime3 = Calendar.getInstance();
        startTime3.set(Calendar.HOUR_OF_DAY, 14);
        startTime3.set(Calendar.MINUTE, 0);
        endTime3.set(Calendar.HOUR_OF_DAY, 15);
        endTime3.set(Calendar.MINUTE, 0);
        DrawableCalendarEvent event3 = new DrawableCalendarEvent("Meeting 15h00 - 17h00", "", "Bordeaux",
                ContextCompat.getColor(this.getContext(), R.color.Blue_Grey), startTime3, endTime3, false, R.mipmap.icon_launcher);
        eventList.add(event3);
    }

    public class APIRequestCalendarEvent extends AsyncTask<String, Void, List<ContentValues>> {

        @Override
        protected void onPostExecute(List<ContentValues> result)
        {
            super.onPostExecute(result);
            try {
                fillListEvent(result);
            } catch (ParseException p){
                Log.e("Parsing Date", "Parsing Error ", p);
            }

        }

        @Override
        protected List<ContentValues> doInBackground(String ... param)
        {
            String resultAPI;
            Integer APIResponse;
            List<ContentValues> listResult = null;

            try {
                String token = SessionAdapter.getInstance().getToken();
                Log.v("Token :", token);
                APIConnectAdapter.getInstance().startConnection("planning/getmonth/" + token + "/" + param[0], "V0.2");
                APIConnectAdapter.getInstance().setRequestConnection("GET");

                APIResponse = APIConnectAdapter.getInstance().getResponseCode();
                Log.v("Response API :", APIResponse.toString());
                if (APIResponse == 200) {
                    resultAPI = APIConnectAdapter.getInstance().getInputSream();
                    listResult = APIConnectAdapter.getInstance().getMonthPlanning(resultAPI);
                } else {
                    return null;
                }

            } catch (IOException e){
                Log.e("APIConnection", "Error ", e);
                return null;
            } catch (JSONException j){
                Log.e("APIConnection", "Error ", j);
                return null;
            }finally {
                APIConnectAdapter.getInstance().closeConnection();
            }
            return listResult;
        }

    }
}
