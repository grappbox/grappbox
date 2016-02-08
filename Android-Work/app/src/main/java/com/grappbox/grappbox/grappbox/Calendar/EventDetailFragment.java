package com.grappbox.grappbox.grappbox.Calendar;

import android.app.Dialog;
import android.content.ContentValues;
import android.os.Bundle;
import android.support.v4.app.Fragment;
import android.util.Log;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.ArrayAdapter;
import android.widget.Button;
import android.widget.EditText;
import android.widget.SimpleAdapter;
import android.widget.Spinner;
import android.widget.TextView;

import com.grappbox.grappbox.grappbox.Model.SessionAdapter;
import com.grappbox.grappbox.grappbox.NonScrollListView;
import com.grappbox.grappbox.grappbox.R;

import java.text.ParseException;
import java.text.SimpleDateFormat;
import java.util.ArrayList;
import java.util.Calendar;
import java.util.HashMap;
import java.util.Iterator;
import java.util.List;

/**
 * Created by tan_f on 21/01/2016.
 */
public class EventDetailFragment extends EventFragment {

    private int         _idEvent;
    private int         _projectId;
    private String      _eventIcon;
    private View        _rootView;
    private EditText    _eventTitle;
    private EditText    _eventDescription;
    private TextView    _eventBeginDateDay;
    private TextView    _eventBeginDateHour;
    private TextView    _eventEndDateDay;
    private TextView    _eventEndDateHour;
    private Button      _eventUpdateData;
    private Button      _eventAddUserEvent;
    private Button      _eventDeleteEventButton;
    private Spinner     _eventProjectSpinner;
    private NonScrollListView   _eventListUser;
    private ContentValues _eventProjectId = new ContentValues();

    public View onCreateView(LayoutInflater inflater, ViewGroup container,
                             Bundle savedInstanceState) {
        _rootView = inflater.inflate(R.layout.fragment_event_details, container, false);

        _idEvent = getArguments().getInt("idEvent");
        _eventTitle = (EditText)_rootView.findViewById(R.id.event_title);
        _eventDescription = (EditText) _rootView.findViewById(R.id.event_description);
        _eventBeginDateDay = (TextView) _rootView.findViewById(R.id.event_begin_date_day);
        _eventBeginDateDay.setOnClickListener((View v) -> {

            DatePickerFragment datePickerFragment = new DatePickerFragment();
            datePickerFragment.setTextView(_eventBeginDateDay);
            datePickerFragment.show(getFragmentManager(), "datePicker");

        });
        _eventBeginDateHour = (TextView) _rootView.findViewById(R.id.event_begin_date_hour);
        _eventBeginDateHour.setOnClickListener((View v) -> {

            TimePickerFragment timePickerFragment = new TimePickerFragment();
            timePickerFragment.setTextViewHour(_eventBeginDateHour);
            timePickerFragment.show(getFragmentManager(), "timePicker");

        });
        _eventEndDateDay = (TextView) _rootView.findViewById(R.id.event_end_date_day);
        _eventEndDateDay.setOnClickListener((View v) -> {

            DatePickerFragment datePickerFragment = new DatePickerFragment();
            datePickerFragment.setTextView(_eventEndDateDay);
            datePickerFragment.show(getFragmentManager(), "datePicker");

        });
        _eventEndDateHour = (TextView) _rootView.findViewById(R.id.event_end_date_hour);
        _eventEndDateHour.setOnClickListener((View v) -> {
            TimePickerFragment timePickerFragment = new TimePickerFragment();
            timePickerFragment.setTextViewHour(_eventEndDateHour);
            timePickerFragment.show(getFragmentManager(), "timePicker");
        });
        _eventListUser = (NonScrollListView) _rootView.findViewById(R.id.event_list_user);
        _eventUpdateData = (Button) _rootView.findViewById(R.id.event_update_data);
        _eventUpdateData.setOnClickListener((View v) -> {

            sendUpadteRequestToAPI();

        });
        _eventAddUserEvent = (Button) _rootView.findViewById(R.id.event_add_user);
        _eventAddUserEvent.setOnClickListener((View v) -> {
            addUserToEvent();
        });
        _eventDeleteEventButton = (Button) _rootView.findViewById(R.id.event_delete);
        _eventDeleteEventButton.setOnClickListener((View v) -> {
            deleteEvent();
        });
        _eventProjectSpinner = (Spinner) _rootView.findViewById(R.id.event_project);
        APIRequestGetEventData event = new APIRequestGetEventData(this, _idEvent);
        event.execute();
        return _rootView;
    }

    @Override
    public void fillProjectListSpinner(List<ContentValues> project)
    {
        int pos = 0;
        List<String> list = new ArrayList<String>();
        Iterator<ContentValues> it = project.iterator();
        list.add("No project");

        _eventProjectId.put("No project", "-1");
        while (it.hasNext())
        {
            ContentValues item = it.next();
            String projectName = item.get("name").toString();
            list.add(projectName);
            _eventProjectId.put(projectName, item.get("id").toString());
        }
        for (int i = 0; i < project.size(); ++i)
        {
            ContentValues item = project.get(i);
            if (Integer.parseInt(item.get("id").toString()) == _projectId)
                pos = i + 1;
        }
        ArrayAdapter<String> dataAdater = new ArrayAdapter<String>(this.getContext(), android.R.layout.simple_spinner_dropdown_item, list);
        dataAdater.setDropDownViewResource(android.R.layout.simple_spinner_dropdown_item);
        _eventProjectSpinner.setAdapter(dataAdater);
        _eventProjectSpinner.setSelection(pos);
    }

    private void deleteEvent()
    {
        final Dialog eventDelete = new Dialog(getActivity());
        eventDelete.setTitle("Warning ! Delete Event : ");
        eventDelete.setContentView(R.layout.dialog_event_delete);
        Button confirmChangePass = (Button)eventDelete.findViewById(R.id.event_confirm_delete);
        confirmChangePass.setOnClickListener((View v)-> {

            APIRequestDeleteEvent addUser = new APIRequestDeleteEvent(this, _idEvent, eventDelete);
            addUser.execute();

        });
        Button cancelChangePass = (Button)eventDelete.findViewById(R.id.event_cancel_delete);
        cancelChangePass.setOnClickListener((View v) -> {
            eventDelete.dismiss();
        });
        eventDelete.show();
    }

    private void addUserToEvent()
    {
        final Dialog eventAddUserDialog = new Dialog(getActivity());
        eventAddUserDialog.setTitle("Add User : ");
        eventAddUserDialog.setContentView(R.layout.dialog_event_add_user);
        final EditText userMail = (EditText)eventAddUserDialog.findViewById(R.id.event_user_mail);
        Button confirmChangePass = (Button)eventAddUserDialog.findViewById(R.id.event_confirm_add_user);
        confirmChangePass.setOnClickListener((View v)-> {

            APIRequestEventAddUser addUser = new APIRequestEventAddUser(this, _idEvent, eventAddUserDialog);
            addUser.execute(userMail.getText().toString());

        });
        Button cancelChangePass = (Button)eventAddUserDialog.findViewById(R.id.event_cancel_add_user);
        cancelChangePass.setOnClickListener((View v) -> {
                userMail.setText("");
            eventAddUserDialog.dismiss();
        });
        eventAddUserDialog.show();
    }

    private void sendUpadteRequestToAPI()
    {
        String beginDate = _eventBeginDateDay.getText().toString() + " " + _eventBeginDateHour.getText().toString() + ":00";
        String endDate = _eventEndDateDay.getText().toString() + " " + _eventEndDateHour.getText().toString() + ":00";
        APIRequestEventUpadteTask updateEvent = new APIRequestEventUpadteTask(this, _idEvent);
        updateEvent.execute(_eventProjectId.get(_eventProjectSpinner.getSelectedItem().toString()).toString(), _eventTitle.getText().toString(), _eventDescription.getText().toString(), _eventIcon, beginDate, endDate);
    }

    public void fillContentData(ContentValues event, List<ContentValues> userList)
    {
        SimpleDateFormat date = new SimpleDateFormat("yyyy-MM-dd HH:mm:ss");
        SimpleDateFormat hourFormat = new SimpleDateFormat("HH:mm");
        SimpleDateFormat dayFormat = new SimpleDateFormat("yyyy-MM-dd");
        Calendar beginDate = Calendar.getInstance();
        Calendar endDate = Calendar.getInstance();

        try {
            _eventTitle.setText(event.get("title").toString());
            if (!event.get("projectId").toString().equals("null"))
                _projectId = Integer.parseInt(event.get("projectId").toString());
            _eventDescription.setText(event.get("description").toString());
            beginDate.setTime(date.parse(event.get("beginDate").toString()));
            endDate.setTime(date.parse(event.get("endDate").toString()));
            _eventBeginDateDay.setText(dayFormat.format(beginDate.getTime()));
            _eventBeginDateHour.setText(hourFormat.format(beginDate.getTime()));
            _eventEndDateDay.setText(dayFormat.format(endDate.getTime()));
            _eventEndDateHour.setText(hourFormat.format(endDate.getTime()));
            _eventIcon = event.get("icon").toString();

            ArrayList<HashMap<String, String>> listUser = new ArrayList<HashMap<String, String>>();
            for (ContentValues user : userList)
            {
                HashMap<String, String> map = new HashMap<String, String>();
                map.put("event_list_profile_username", user.get("name").toString());
                map.put("event_list_profile_email", user.get("email").toString());
                listUser.add(map);
            }

            SimpleAdapter eventUserListAdapter = new SimpleAdapter(_rootView.getContext(), listUser, R.layout.item_event_user_list,
                    new String[] {"event_list_profile_username", "event_list_profile_email"},
                    new int[] {R.id.event_list_profile_username, R.id.event_list_profile_email});
            _eventListUser.setAdapter(eventUserListAdapter);
        } catch (ParseException p)
        {
            Log.e("Date parse", "Parsing error");
        }
    }

}
