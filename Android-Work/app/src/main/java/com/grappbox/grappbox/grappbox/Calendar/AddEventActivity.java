package com.grappbox.grappbox.grappbox.Calendar;

import android.app.AlertDialog;
import android.content.ContentValues;
import android.content.Context;
import android.os.Bundle;
import android.view.View;
import android.widget.AdapterView;
import android.widget.ArrayAdapter;
import android.widget.Button;
import android.widget.EditText;
import android.widget.Spinner;
import android.widget.TextView;

import com.grappbox.grappbox.grappbox.R;

import java.text.SimpleDateFormat;
import java.util.ArrayList;
import java.util.Calendar;
import java.util.Iterator;
import java.util.List;

/**
 * Created by tan_f on 13/07/2016.
 */
public class AddEventActivity extends EventActivity {

    private Context _context = this;
    private EditText _eventTitle;
    private EditText    _eventDescription;
    private TextView _eventBeginDateDay;
    private TextView    _eventBeginDateHour;
    private TextView    _eventEndDateDay;
    private TextView    _eventEndDateHour;
    private Button _eventSendButton;
    private Spinner _eventProjectSpinner;
    private Spinner     _eventTypes;
    private ContentValues _eventProjectId = new ContentValues();

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.fragment_add_event);

        _eventTitle = (EditText) findViewById(R.id.create_title_event);
        _eventDescription = (EditText) findViewById(R.id.create_event_description);
        _eventBeginDateDay = (TextView) findViewById(R.id.create_event_begin_date_day);
        _eventBeginDateHour = (TextView) findViewById(R.id.create_event_begin_date_hour);
        _eventEndDateDay = (TextView) findViewById(R.id.create_event_end_date_day);
        _eventEndDateHour = (TextView) findViewById(R.id.create_event_end_date_hour);
        _eventSendButton = (Button) findViewById(R.id.create_event_button);
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
        initDateValue();
        setListener();
        APIrequestGetUserProject getProject = new APIrequestGetUserProject(this);
        getProject.execute();
    }

    @Override
    public void fillProjectListSpinner(List<ContentValues> project)
    {
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

        ArrayAdapter<String> dataAdater = new ArrayAdapter<String>(this, android.R.layout.simple_spinner_dropdown_item, list);
        dataAdater.setDropDownViewResource(android.R.layout.simple_spinner_dropdown_item);
        _eventProjectSpinner.setAdapter(dataAdater);
    }

    private void initDateValue()
    {
        final SimpleDateFormat dateForm = new SimpleDateFormat("yyyy-MM-dd");
        final SimpleDateFormat hourForm = new SimpleDateFormat("HH:mm");
        Calendar calendar = Calendar.getInstance();

        _eventBeginDateDay.setText(dateForm.format(calendar.getTime()));
        _eventBeginDateHour.setText(hourForm.format(calendar.getTime()));
        _eventEndDateDay.setText(dateForm.format(calendar.getTime()));
        _eventEndDateHour.setText(hourForm.format(calendar.getTime()));
    }

    private void setListener()
    {
        _eventBeginDateDay.setOnClickListener((View v) -> {

            DatePickerFragment datePickerFragment = new DatePickerFragment();
            datePickerFragment.setTextView(_eventBeginDateDay);
            datePickerFragment.show(getSupportFragmentManager(), "datePicker");

        });
        _eventBeginDateHour.setOnClickListener((View v) -> {

            TimePickerFragment timePickerFragment = new TimePickerFragment();
            timePickerFragment.setTextViewHour(_eventBeginDateHour);
            timePickerFragment.show(getSupportFragmentManager(), "timePicker");

        });
        _eventEndDateDay.setOnClickListener((View v) -> {

            DatePickerFragment datePickerFragment = new DatePickerFragment();
            datePickerFragment.setTextView(_eventEndDateDay);
            datePickerFragment.show(getSupportFragmentManager(), "datePicker");

        });
        _eventEndDateHour.setOnClickListener((View v) -> {
            TimePickerFragment timePickerFragment = new TimePickerFragment();
            timePickerFragment.setTextViewHour(_eventEndDateHour);
            timePickerFragment.show(getSupportFragmentManager(), "timePicker");
        });
        _eventSendButton.setOnClickListener((View v) -> {
            addEvent();
        });
    }

    private void addEvent()
    {
        String beginDate = _eventBeginDateDay.getText().toString() + " " + _eventBeginDateHour.getText().toString() + ":00";
        String endDate = _eventEndDateDay.getText().toString() + " " + _eventEndDateHour.getText().toString() + ":00";

        APIRequestAddEvent event = new APIRequestAddEvent(this);
        event.execute(_eventProjectId.get(_eventProjectSpinner.getSelectedItem().toString()).toString(), _eventTitle.getText().toString(), _eventDescription.getText().toString(), beginDate, endDate);
    }
}
