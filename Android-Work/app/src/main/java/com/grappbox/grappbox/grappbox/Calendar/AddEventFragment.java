package com.grappbox.grappbox.grappbox.Calendar;

import android.content.ContentValues;
import android.os.AsyncTask;
import android.os.Bundle;
import android.support.v4.app.Fragment;
import android.util.Log;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.ArrayAdapter;
import android.widget.Button;
import android.widget.EditText;
import android.widget.Spinner;
import android.widget.TextView;

import com.grappbox.grappbox.grappbox.Model.APIConnectAdapter;
import com.grappbox.grappbox.grappbox.Model.SessionAdapter;
import com.grappbox.grappbox.grappbox.R;

import org.w3c.dom.Text;

import java.io.IOException;
import java.text.Format;
import java.text.SimpleDateFormat;
import java.util.ArrayList;
import java.util.Calendar;
import java.util.Iterator;
import java.util.List;
import java.util.Map;
import java.util.Set;

/**
 * Created by tan_f on 21/01/2016.
 */
public class AddEventFragment extends EventFragment {

    private View        _rootView;
    private EditText    _eventTitle;
    private EditText    _eventDescription;
    private TextView    _eventBeginDateDay;
    private TextView    _eventBeginDateHour;
    private TextView    _eventEndDateDay;
    private TextView    _eventEndDateHour;
    private Button      _eventSendButton;
    private Spinner     _eventProjectSpinner;
    private ContentValues _eventProjectId = new ContentValues();

    public View onCreateView(LayoutInflater inflater, ViewGroup container,
                             Bundle savedInstanceState) {
        _rootView = inflater.inflate(R.layout.fragment_add_event, container, false);

        _eventTitle = (EditText) _rootView.findViewById(R.id.create_title_event);
        _eventDescription = (EditText) _rootView.findViewById(R.id.create_event_description);
        _eventBeginDateDay = (TextView) _rootView.findViewById(R.id.create_event_begin_date_day);
        _eventBeginDateHour = (TextView) _rootView.findViewById(R.id.create_event_begin_date_hour);
        _eventEndDateDay = (TextView) _rootView.findViewById(R.id.create_event_end_date_day);
        _eventEndDateHour = (TextView) _rootView.findViewById(R.id.create_event_end_date_hour);
        _eventSendButton = (Button) _rootView.findViewById(R.id.create_event_button);
        _eventProjectSpinner = (Spinner) _rootView.findViewById(R.id.event_project);

        initDateValue();
        setListener();
        APIrequestGetUserProject getProject = new APIrequestGetUserProject(this);
        getProject.execute();
        return _rootView;
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

        ArrayAdapter<String> dataAdater = new ArrayAdapter<String>(this.getContext(), android.R.layout.simple_spinner_dropdown_item, list);
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
            datePickerFragment.show(getFragmentManager(), "datePicker");

        });
        _eventBeginDateHour.setOnClickListener((View v) -> {

            TimePickerFragment timePickerFragment = new TimePickerFragment();
            timePickerFragment.setTextViewHour(_eventBeginDateHour);
            timePickerFragment.show(getFragmentManager(), "timePicker");

        });
        _eventEndDateDay.setOnClickListener((View v) -> {

            DatePickerFragment datePickerFragment = new DatePickerFragment();
            datePickerFragment.setTextView(_eventEndDateDay);
            datePickerFragment.show(getFragmentManager(), "datePicker");

        });
        _eventEndDateHour.setOnClickListener((View v) -> {
            TimePickerFragment timePickerFragment = new TimePickerFragment();
            timePickerFragment.setTextViewHour(_eventEndDateHour);
            timePickerFragment.show(getFragmentManager(), "timePicker");
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
