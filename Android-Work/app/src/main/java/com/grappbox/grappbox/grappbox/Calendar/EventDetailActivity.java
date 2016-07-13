package com.grappbox.grappbox.grappbox.Calendar;

import android.app.AlertDialog;
import android.app.Dialog;
import android.content.ContentValues;
import android.content.Context;
import android.content.Intent;
import android.os.Bundle;
import android.util.Log;
import android.view.View;
import android.widget.AdapterView;
import android.widget.ArrayAdapter;
import android.widget.Button;
import android.widget.EditText;
import android.widget.SimpleAdapter;
import android.widget.Spinner;
import android.widget.TextView;

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
 * Created by tan_f on 13/07/2016.
 */
public class EventDetailActivity extends EventActivity {

    private int                 _idEvent;
    private int                 _projectId;
    private Context             _context = this;
    private String              _eventIcon;
    private EditText            _eventTitle;
    private EditText            _eventDescription;
    private TextView            _eventBeginDateDay;
    private TextView            _eventBeginDateHour;
    private TextView            _eventEndDateDay;
    private TextView            _eventEndDateHour;
    private Button              _eventUpdateData;
    private Button              _eventAddUserEvent;
    private Button              _eventDeleteEventButton;
    private Spinner             _eventProjectSpinner;
    private Spinner             _eventTypes;
    private NonScrollListView   _eventListUser;
    private ContentValues       _eventProjectId = new ContentValues();

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.fragment_event_details);

        Intent intent = getIntent();

        _idEvent = intent.getExtras().getInt("idEvent");
        _eventTitle = (EditText) findViewById(R.id.event_title);
        _eventDescription = (EditText) findViewById(R.id.event_description);
        _eventBeginDateDay = (TextView) findViewById(R.id.event_begin_date_day);
        if (_eventBeginDateDay != null) {
            _eventBeginDateDay.setOnClickListener((View v) -> {

                DatePickerFragment datePickerFragment = new DatePickerFragment();
                datePickerFragment.setTextView(_eventBeginDateDay);
                datePickerFragment.show(getSupportFragmentManager(), "datePicker");

            });
        }
        _eventBeginDateHour = (TextView) findViewById(R.id.event_begin_date_hour);
        if (_eventBeginDateHour != null) {
            _eventBeginDateHour.setOnClickListener((View v) -> {

                TimePickerFragment timePickerFragment = new TimePickerFragment();
                timePickerFragment.setTextViewHour(_eventBeginDateHour);
                timePickerFragment.show(getSupportFragmentManager(), "timePicker");

            });
        }
        _eventEndDateDay = (TextView) findViewById(R.id.event_end_date_day);
        if (_eventEndDateDay != null) {
            _eventEndDateDay.setOnClickListener((View v) -> {

                DatePickerFragment datePickerFragment = new DatePickerFragment();
                datePickerFragment.setTextView(_eventEndDateDay);
                datePickerFragment.show(getSupportFragmentManager(), "datePicker");

            });
        }
        _eventEndDateHour = (TextView) findViewById(R.id.event_end_date_hour);
        if (_eventEndDateHour != null) {
            _eventEndDateHour.setOnClickListener((View v) -> {
                TimePickerFragment timePickerFragment = new TimePickerFragment();
                timePickerFragment.setTextViewHour(_eventEndDateHour);
                timePickerFragment.show(getSupportFragmentManager(), "timePicker");
            });
        }
        _eventListUser = (NonScrollListView) findViewById(R.id.event_list_user);
        _eventUpdateData = (Button) findViewById(R.id.event_update_data);
        _eventUpdateData.setOnClickListener((View v) -> {

            sendUpadteRequestToAPI();

        });
        _eventAddUserEvent = (Button) findViewById(R.id.event_add_user);
        _eventAddUserEvent.setOnClickListener((View v) -> {
            addUserToEvent();
        });
        _eventDeleteEventButton = (Button) findViewById(R.id.event_delete);
        _eventDeleteEventButton.setOnClickListener((View v) -> {
            deleteEvent();
        });
        _eventProjectSpinner = (Spinner) findViewById(R.id.event_project);
        _eventTypes = (Spinner) findViewById(R.id.event_type);
        ArrayAdapter<CharSequence> eventTypeAdapter = ArrayAdapter.createFromResource(this, R.array.event_types_list_default, android.R.layout.simple_spinner_dropdown_item);
        eventTypeAdapter.setDropDownViewResource(android.R.layout.simple_spinner_dropdown_item);
        _eventTypes.setAdapter(eventTypeAdapter);
        _eventTypes.setOnItemSelectedListener(new AdapterView.OnItemSelectedListener() {
            @Override
            public void onItemSelected(AdapterView<?> parent, View view, int position, long id) {
                final String newType = getResources().getStringArray(R.array.event_types_list_default)[1];
                if (_eventTypes.getSelectedItem().toString().equals(newType)){
                    AlertDialog.Builder build = new AlertDialog.Builder(_context);
                    build.setTitle("Create new type");
                    build.show();
                }
            }

            @Override
            public void onNothingSelected(AdapterView<?> parent) {

            }
        });
        APIRequestGetEventData event = new APIRequestGetEventData(this, _idEvent);
        event.execute();
    }

    @Override
    public void onPause()
    {
        super.onPause();
        getCallingActivity();
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
        ArrayAdapter<String> dataAdater = new ArrayAdapter<String>(this, android.R.layout.simple_spinner_dropdown_item, list);
        dataAdater.setDropDownViewResource(android.R.layout.simple_spinner_dropdown_item);
        _eventProjectSpinner.setAdapter(dataAdater);
        _eventProjectSpinner.setSelection(pos);
    }

    private void addUserToEvent()
    {
        AlertDialog.Builder addUser = new AlertDialog.Builder(this);
        addUser.setMessage("");


        /*
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
        eventAddUserDialog.show();*/
    }

    private void deleteEvent()
    {
        final Dialog eventDelete = new Dialog(this);
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

            SimpleAdapter eventUserListAdapter = new SimpleAdapter(_context, listUser, R.layout.item_event_user_list,
                    new String[] {"event_list_profile_username", "event_list_profile_email"},
                    new int[] {R.id.event_list_profile_username, R.id.event_list_profile_email});
            _eventListUser.setAdapter(eventUserListAdapter);
        } catch (ParseException p)
        {
            Log.e("Date parse", "Parsing error");
        }
    }

}
