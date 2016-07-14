package com.grappbox.grappbox.grappbox.Calendar;

import android.app.Activity;
import android.app.ProgressDialog;
import android.content.ContentValues;
import android.content.Intent;
import android.os.AsyncTask;
import android.os.Bundle;
import android.support.design.widget.FloatingActionButton;
import android.support.v4.app.Fragment;
import android.support.v4.content.ContextCompat;
import android.support.v4.widget.SwipeRefreshLayout;
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
import com.grappbox.grappbox.grappbox.Model.LoadingFragment;
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
public class AgendaFragment extends LoadingFragment implements CalendarPickerController {

    private static final String LOG_TAG = AgendaFragment.class.getSimpleName();
    private static final int ADD_EVENT_RESULT = 1;
    private static final int EVENT_DETAIL = 2;

    private View _rootView;
    private View _frameView;
    private AgendaCalendarView _AgendaCalendarView;
    private FloatingActionButton _FAB;
    private Calendar _minDate = Calendar.getInstance();
    private Calendar _maxDate = Calendar.getInstance();
//    private ProgressDialog  _progress;

    public View onCreateView(LayoutInflater inflater, ViewGroup container,
                             Bundle savedInstanceState) {
        _rootView = inflater.inflate(R.layout.fragment_agenda_calendar, container, false);

        _frameView = _rootView.findViewById(R.id.frame_view);
        startLoading(_rootView, R.id.loader, _frameView);
        ButterKnife.bind(this.getActivity());

        _minDate.add(Calendar.YEAR, -1);
        _minDate.set(Calendar.DAY_OF_MONTH, 1);
        _maxDate.add(Calendar.YEAR, 3);

        _AgendaCalendarView = (AgendaCalendarView)_rootView.findViewById(R.id.agenda_calendar_view);

        _FAB = (FloatingActionButton) _rootView.findViewById(R.id.add_event_float_button);
        _FAB.setOnClickListener((View v)-> {
            Intent intent = new Intent(this.getActivity(), AddEventActivity.class);
            startActivityForResult(intent, ADD_EVENT_RESULT);
        });
        _FAB.hide();

        CalendarAPICallRefresh();
        return _rootView;
    }

    private void CalendarAPICallRefresh()
    {
        SimpleDateFormat format = new SimpleDateFormat("yyyy-MM-dd");

        Calendar cal = Calendar.getInstance();
        cal.set(cal.get(Calendar.YEAR), cal.get(Calendar.MONTH) - 1, 1);
        String previousMonth = format.format(cal.getTime());

        cal.set(cal.get(Calendar.YEAR), cal.get(Calendar.MONTH) + 1, 1);
        String currentDate = format.format(cal.getTime());

        cal.set(cal.get(Calendar.YEAR), cal.get(Calendar.MONTH) + 1, 1);
        String nextMonth = format.format(cal.getTime());
        Log.v("Date = ", currentDate);
        APIRequestCalendarEvent api = new APIRequestCalendarEvent();
        api.execute(previousMonth, currentDate, nextMonth);
    }

    @Override
    public void onDaySelected(DayItem dayItem) {
        Log.d(LOG_TAG, String.format("Selected day: %s", dayItem));
    }

    @Override
    public void onEventSelected(CalendarEvent event) {
        if (!event.getTitle().contains("No events"))
        {
            Intent intent = new Intent(this.getActivity(), EventDetailActivity.class);
            intent.putExtra("idEvent", (int)event.getId());
            startActivityForResult(intent, EVENT_DETAIL);
//            startActivity(intent);
        }
    }

    @Override
    public void onActivityResult(int requestCode, int resultCode, Intent data){

        if ((requestCode == ADD_EVENT_RESULT || requestCode == EVENT_DETAIL)){
            AgendaFragment agendaFragment = new AgendaFragment();
            getFragmentManager().beginTransaction().remove(this).commit();
            getFragmentManager().beginTransaction().replace(R.id.content_frame, agendaFragment).commit();
        }

    }

    private void fillListEvent(List<ContentValues> eventList) throws ParseException {

        SimpleDateFormat format = new SimpleDateFormat("yyyy-MM-dd HH:mm:ss");
        SimpleDateFormat hourFormat = new SimpleDateFormat("HH:mm");
        List<CalendarEvent> calendarEventList = new ArrayList<>();
        if (eventList != null) {
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
                } else {
                    calendarEvent = new BaseCalendarEvent(Long.parseLong(event.get("id").toString()), ContextCompat.getColor(this.getContext(), R.color.Brown), event.get("title").toString(),
                            "", "", beginDate.getTimeInMillis(), endDate.getTimeInMillis(), 0, "");
                }

                calendarEventList.add(calendarEvent);
            }
        }
        endLoading();
        _FAB.show();
        _AgendaCalendarView.init(calendarEventList, _minDate, _maxDate, Locale.getDefault(), this);
        _AgendaCalendarView.addEventRenderer(new DrawableEventRenderer());
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
                String token = SessionAdapter.getInstance().getUserData(SessionAdapter.KEY_TOKEN);
                Log.v("Token :", token);
                APIConnectAdapter.getInstance().startConnection("planning/getmonth/" + token + "/" + param[1], "V0.2");
                APIConnectAdapter.getInstance().setRequestConnection("GET");

                APIResponse = APIConnectAdapter.getInstance().getResponseCode();
                Log.v("Response API :", APIResponse.toString());
                if (APIResponse == 200) {
                    resultAPI = APIConnectAdapter.getInstance().getInputSream();
                    Log.v("API Content:", resultAPI);
                    listResult = APIConnectAdapter.getInstance().getMonthPlanning(resultAPI);
                }

                APIConnectAdapter.getInstance().startConnection("planning/getmonth/" + token + "/" + param[0], "V0.2");
                APIResponse = APIConnectAdapter.getInstance().getResponseCode();
                Log.v("Response API :", APIResponse.toString());
                if (APIResponse == 200) {
                    List<ContentValues> previousMonth;
                    resultAPI = APIConnectAdapter.getInstance().getInputSream();
                    Log.v("API Content:", resultAPI);
                    previousMonth = APIConnectAdapter.getInstance().getMonthPlanning(resultAPI);
                    if (listResult == null)
                        listResult = previousMonth;
                    listResult.addAll(previousMonth);
                }

                APIConnectAdapter.getInstance().startConnection("planning/getmonth/" + token + "/" + param[2], "V0.2");
                APIResponse = APIConnectAdapter.getInstance().getResponseCode();
                Log.v("Response API :", APIResponse.toString());
                if (APIResponse == 200) {
                    List<ContentValues> nextMonth;
                    resultAPI = APIConnectAdapter.getInstance().getInputSream();
                    Log.v("API Content:", resultAPI);
                    nextMonth = APIConnectAdapter.getInstance().getMonthPlanning(resultAPI);
                    if (listResult == null)
                        listResult = nextMonth;
                    listResult.addAll(nextMonth);
                }

            } catch (IOException | JSONException e){
                Log.e("APIConnection", "Error ", e);
                return null;
            }finally {
                APIConnectAdapter.getInstance().closeConnection();
            }
            return listResult;
        }

    }
}
