package com.grappbox.grappbox.grappbox.Calendar;

import android.app.AlertDialog;
import android.app.Dialog;
import android.content.ContentValues;
import android.content.Context;
import android.content.DialogInterface;
import android.content.Intent;
import android.os.Bundle;
import android.util.Log;
import android.view.LayoutInflater;
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
import java.util.Vector;

/**
 * Created by tan_f on 13/07/2016.
 */
public class EventDetailActivity extends EventActivity {

    private int                 _idEvent;
    private int                 _projectId;
    private String              _projecTypeEvent;
    private Vector<String>      _mailList = new Vector<String>();
    private EventDetailActivity _context = this;
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
    private ContentValues       _eventListTypes = new ContentValues();

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
        if (_eventUpdateData != null) {
            _eventUpdateData.setOnClickListener((View v) -> {
                sendUpadteRequestToAPI();

            });
        }
        _eventAddUserEvent = (Button) findViewById(R.id.event_add_user);
        if (_eventAddUserEvent != null) {
            _eventAddUserEvent.setOnClickListener((View v) -> {
                addUserToEvent();
            });
        }
        _eventDeleteEventButton = (Button) findViewById(R.id.event_delete);
        if (_eventDeleteEventButton != null) {
            _eventDeleteEventButton.setOnClickListener((View v) -> {
                deleteEvent();
            });
        }
        _eventProjectSpinner = (Spinner) findViewById(R.id.event_project);
        _eventTypes = (Spinner) findViewById(R.id.event_type);

        APIRequestGetEventData event = new APIRequestGetEventData(this, _idEvent);
        event.execute();
        APIRequestGetEventType getEventType = new APIRequestGetEventType(this);
        getEventType.execute();
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

    @Override
    public void fillEventListSpinner(List<ContentValues> eventTypes)
    {
        int pos = 0;
        List<String> list = new ArrayList<>();
        Iterator<ContentValues> it = eventTypes.iterator();
        list.add("Nothing");

        _eventListTypes.put("Nothing", "-1");
        while (it.hasNext())
        {
            ContentValues item = it.next();
            String eventTypesName = item.get("name").toString();
            list.add(eventTypesName);
            _eventListTypes.put(eventTypesName, item.get("id").toString());

        }
        for (int i = 0; i < eventTypes.size(); ++i)
        {
            ContentValues item = eventTypes.get(i);
            if (item.get("name").toString().equals(_projecTypeEvent))
                pos = i + 1;
        }
        list.add("New type");
        _eventListTypes.put("New type", "-2");
        ArrayAdapter<String> eventTypeAdapter = new ArrayAdapter<String>(this, android.R.layout.simple_spinner_dropdown_item, list);
        eventTypeAdapter.setDropDownViewResource(android.R.layout.simple_spinner_dropdown_item);
        _eventTypes.setAdapter(eventTypeAdapter);
        _eventTypes.setOnItemSelectedListener(new AdapterView.OnItemSelectedListener() {
            @Override
            public void onItemSelected(AdapterView<?> parent, View view, int position, long id) {
                final String newType = getResources().getStringArray(R.array.event_types_list_default)[1];
                if (_eventTypes.getSelectedItem().toString().equals(newType)){
/*                    AlertDialog.Builder build = new AlertDialog.Builder(_context);
                    build.setTitle("Create new type");
                    build.show();*/
                }
            }

            @Override
            public void onNothingSelected(AdapterView<?> parent) {

            }
        });
        _eventTypes.setSelection(pos);
    }

    private void addUserToEvent()
    {
        AlertDialog.Builder addUser = new AlertDialog.Builder(this);
        addUser.setTitle("Add User : ");
        LayoutInflater inflater = getLayoutInflater();
        View view = inflater.inflate(R.layout.dialog_add_user_by_mail, null);
        addUser.setView(view);
        addUser.setPositiveButton(R.string.positive_response, new DialogInterface.OnClickListener() {
            @Override
            public void onClick(DialogInterface dialog, int which) {
                EditText mail =  (EditText)view.findViewById(R.id.mail_user);

                APIRequestEventAddUser addUser = new APIRequestEventAddUser(_context, _idEvent, true);
                addUser.execute(mail.getText().toString());
            }
        });
        addUser.setNegativeButton(R.string.negative_response, new DialogInterface.OnClickListener() {
            @Override
            public void onClick(DialogInterface dialog, int which) {

            }
        });
        addUser.show();
    }

    private void deleteEvent()
    {
        AlertDialog.Builder builder = new AlertDialog.Builder(this);
        builder.setTitle("Delete Event : ");
        builder.setMessage("Are you sure you want to delete this event ?");
        builder.setPositiveButton(R.string.positive_response, new DialogInterface.OnClickListener() {
            @Override
            public void onClick(DialogInterface dialog, int which) {

                APIRequestDeleteEvent deleteEvent = new APIRequestDeleteEvent(_context, _idEvent);
                deleteEvent.execute();
            }
        });
        builder.setNegativeButton(R.string.negative_response, new DialogInterface.OnClickListener() {
            @Override
            public void onClick(DialogInterface dialog, int which) {

            }
        });
        builder.show();
    }

    private void sendUpadteRequestToAPI()
    {

        String typesEvent = _eventListTypes.get(_eventTypes.getSelectedItem().toString()).toString();

        if (_eventTitle.getText().toString().equals(""))
        {
            AlertDialog.Builder build = new AlertDialog.Builder(_context);
            build.setTitle("Event Title");
            build.setMessage("Put a title for the event");
            build.show();
            return ;
        }

        if (typesEvent.equals("-1") || typesEvent.equals("-2")){
            AlertDialog.Builder build = new AlertDialog.Builder(_context);
            build.setTitle("Event Type");
            build.setMessage("Select the type of the event");
            build.show();
            return ;
        }

        String beginDate = _eventBeginDateDay.getText().toString() + " " + _eventBeginDateHour.getText().toString() + ":00";
        String endDate = _eventEndDateDay.getText().toString() + " " + _eventEndDateHour.getText().toString() + ":00";
        APIRequestEventUpadteTask updateEvent = new APIRequestEventUpadteTask(this, _idEvent);
        updateEvent.execute(_eventProjectId.get(_eventProjectSpinner.getSelectedItem().toString()).toString(),
                _eventTitle.getText().toString(),
                _eventDescription.getText().toString(),
                _eventIcon,
                beginDate,
                endDate,
                typesEvent);
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
            _projecTypeEvent = event.get("type").toString();

            _mailList.clear();
            ArrayList<HashMap<String, String>> listUser = new ArrayList<HashMap<String, String>>();
            for (ContentValues user : userList)
            {
                HashMap<String, String> map = new HashMap<String, String>();
                map.put("event_list_profile_username", user.get("name").toString());
                map.put("event_list_profile_email", user.get("email").toString());
                _mailList.add(user.get("email").toString());
                listUser.add(map);
            }

            SimpleAdapter eventUserListAdapter = new SimpleAdapter(_context, listUser, R.layout.item_event_user_list,
                    new String[] {"event_list_profile_username", "event_list_profile_email"},
                    new int[] {R.id.event_list_profile_username, R.id.event_list_profile_email});
            _eventListUser.setAdapter(eventUserListAdapter);
            _eventListUser.setOnItemClickListener(new AdapterView.OnItemClickListener() {
                @Override
                public void onItemClick(AdapterView<?> parent, View view, int position, long id) {
                    String mail = _mailList.get(position);
                    AlertDialog.Builder builder = new AlertDialog.Builder(_context);
                    builder.setTitle("Remove event participant : ");

                    Log.v("_mailList.size", String.valueOf(_mailList.size()));
                    if (_mailList.size() <= 1){
                        builder.setMessage("Event need a minimum of 1 participant");
                        builder.setNegativeButton(R.string.negative_response, new DialogInterface.OnClickListener() {
                            @Override
                            public void onClick(DialogInterface dialog, int which) {

                            }
                        });
                    } else {
                        builder.setMessage("Are you sure you want to remove " + mail + " of this event ?");
                        builder.setPositiveButton(R.string.positive_response, new DialogInterface.OnClickListener() {
                            @Override
                            public void onClick(DialogInterface dialog, int which) {
                                APIRequestEventAddUser addUser = new APIRequestEventAddUser(_context, _idEvent, false);
                                addUser.execute(mail);
                            }
                        });
                        builder.setNegativeButton(R.string.negative_response, new DialogInterface.OnClickListener() {
                            @Override
                            public void onClick(DialogInterface dialog, int which) {

                            }
                        });
                    }
                    builder.show();
                }
            });
        } catch (ParseException p)
        {
            Log.e("Date parse", "Parsing error");
        }
    }

}
