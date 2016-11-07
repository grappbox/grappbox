package com.grappbox.grappbox.calendar_fragment;

import android.app.DatePickerDialog;
import android.app.Dialog;
import android.app.DialogFragment;
import android.app.TimePickerDialog;
import android.content.DialogInterface;
import android.content.Intent;
import android.graphics.drawable.ColorDrawable;
import android.os.Build;
import android.os.Bundle;
import android.os.Handler;
import android.support.annotation.Nullable;
import android.support.v4.content.ContextCompat;
import android.support.v7.app.AlertDialog;
import android.support.v7.app.AppCompatActivity;
import android.support.v7.widget.Toolbar;
import android.text.format.DateFormat;
import android.view.Menu;
import android.view.MenuItem;
import android.view.View;
import android.widget.DatePicker;
import android.widget.EditText;
import android.widget.TextView;
import android.widget.TimePicker;

import com.grappbox.grappbox.R;
import com.grappbox.grappbox.receiver.CalendarEventReceiver;
import com.grappbox.grappbox.sync.GrappboxJustInTimeService;


import java.util.Calendar;

/**
 * Created by tan_f on 04/11/2016.
 */

public class NewEventActivity extends AppCompatActivity implements DatePickerDialog.OnDateSetListener, TimePickerDialog.OnTimeSetListener{
    private static final String LOG_TAG = NewEventActivity.class.getSimpleName();

    private EditText mTitle;
    private EditText mDescription;
    private TextView mEventBegin;
    private TextView mEventEnd;
    private CalendarEventReceiver mReceiver;
    private static boolean _isBegin;

    @Override
    protected void onCreate(@Nullable Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setTheme(R.style.CalendarTheme);
        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.LOLLIPOP) {
            getWindow().setStatusBarColor(ContextCompat.getColor(this, R.color.GrappBlue));
        }
        setContentView(R.layout.activity_new_event);
        Toolbar toolbar = (Toolbar)findViewById(R.id.toolbar);
        setSupportActionBar(toolbar);
        getSupportActionBar().setDisplayHomeAsUpEnabled(true);
        getSupportActionBar().setBackgroundDrawable(new ColorDrawable(ContextCompat.getColor(this, R.color.GrappBlue)));
        getSupportActionBar().setHomeAsUpIndicator(ContextCompat.getDrawable(this, R.drawable.ic_cross));
        mTitle = (EditText) findViewById(R.id.title);
        mDescription = (EditText) findViewById(R.id.description);
        mEventBegin = (TextView)findViewById(R.id.event_begin);
        mEventBegin.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                _isBegin = true;
                DatePickerFragment datePicker = new DatePickerFragment();
                datePicker.show(getFragmentManager(), "Date begin");
            }
        });
        mEventEnd = (TextView)findViewById(R.id.event_end);
        mEventEnd.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                _isBegin = false;
                DatePickerFragment datePicker = new DatePickerFragment();
                datePicker.show(getFragmentManager(), "Date end");
            }
        });
    }

    public void registerActivityActionCallback(CalendarEventReceiver.Callback action) {
        if (mReceiver == null)
            mReceiver = new CalendarEventReceiver(this, new Handler());
        mReceiver.registerCallback(action);
    }

    @Override
    public boolean onCreateOptionsMenu(Menu menu) {
        getMenuInflater().inflate(R.menu.calendar_new_event_menu, menu);
        return super.onCreateOptionsMenu(menu);
    }

    private void actionCancel(){
        AlertDialog.Builder buidler = new AlertDialog.Builder(this, R.style.BugtrackerDialogOverride);

        buidler.setTitle(R.string.dialog_cancel_without_saving_title);
        buidler.setMessage(R.string.dialog_cancel_without_saving);
        buidler.setPositiveButton(R.string.quit_word, new DialogInterface.OnClickListener() {
            @Override
            public void onClick(DialogInterface dialog, int which) {
                onBackPressed();
                dialog.dismiss();
            }
        });
        buidler.setNegativeButton(R.string.negative_response, new DialogInterface.OnClickListener() {
            @Override
            public void onClick(DialogInterface dialog, int which) {
                dialog.dismiss();
            }
        });
        buidler.show();
    }

    private void actionSave(){
        Intent save = new Intent(this, GrappboxJustInTimeService.class);
        save.setAction(GrappboxJustInTimeService.ACTION_CREATE_EVENT);
        /*Intent save = new Intent(this, GrappboxJustInTimeService.class);
        save.setAction(mIsEditMode ? GrappboxJustInTimeService.ACTION_EDIT_BUG : GrappboxJustInTimeService.ACTION_CREATE_BUG);
        save.putExtra(mIsEditMode ? GrappboxJustInTimeService.EXTRA_BUG_ID : GrappboxJustInTimeService.EXTRA_PROJECT_ID, mIsEditMode ? mModel._id : mProjectID);
        save.putExtra(GrappboxJustInTimeService.EXTRA_TITLE, mTitle.getText().toString());
        save.putExtra(GrappboxJustInTimeService.EXTRA_DESCRIPTION, mDescription.getText().toString());
        save.putExtra(GrappboxJustInTimeService.EXTRA_CLIENT_ACTION, false);
        save.putExtra(GrappboxJustInTimeService.EXTRA_RESPONSE_RECEIVER, receiver);
        startService(save);*/
    }

    @Override
    public boolean onOptionsItemSelected(MenuItem item) {
        switch (item.getItemId()){
            case android.R.id.home:
                actionCancel();
                return true;
            case R.id.action_save:
                actionSave();
                return true;
        }
        return super.onOptionsItemSelected(item);
    }

    @Override
    public void onDateSet(DatePicker view, int year, int month, int dayOfMonth) {
        String date = String.valueOf(year) + "-" + String.valueOf(month) + "-" + String.valueOf(dayOfMonth);

        if (_isBegin){
            mEventBegin.setText(date);
            TimePickerFragment timepicker = new TimePickerFragment();
            timepicker.show(getFragmentManager(), "Hour begin");
        } else {
            mEventEnd.setText(date);
            TimePickerFragment timepicker = new TimePickerFragment();
            timepicker.show(getFragmentManager(), "Hour end");
        }
    }

    @Override
    public void onTimeSet(TimePicker view, int hourOfDay, int minute) {
        String hour = String.valueOf(hourOfDay) + ":" + String.valueOf(minute);
        if (_isBegin) {
            mEventBegin.setText(mEventBegin.getText() + " " + hour);
        } else {
            mEventEnd.setText(mEventEnd.getText() + " " + hour);
        }
    }

    public static class DatePickerFragment extends DialogFragment{

        @Override
        public Dialog onCreateDialog(Bundle savedInstanceState) {
            final Calendar c =Calendar.getInstance();
            int year = c.get(Calendar.YEAR);
            int month = c.get(Calendar.MONTH);
            int day = c.get(Calendar.DAY_OF_MONTH);

            return new DatePickerDialog(getActivity(), (NewEventActivity)getActivity(), year, month, day);
        }
    }

    public static class TimePickerFragment extends DialogFragment {

        @Override
        public Dialog onCreateDialog(Bundle savedInstanceState) {
            final Calendar c = Calendar.getInstance();
            int hour = c.get(Calendar.HOUR_OF_DAY);
            int minute = c.get(Calendar.MINUTE);

            // Create a new instance of TimePickerDialog and return it
            return new TimePickerDialog(getActivity(), (NewEventActivity)getActivity(), hour, minute,
                    DateFormat.is24HourFormat(getActivity()));
        }
    }

}
