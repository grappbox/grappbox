package com.grappbox.grappbox.grappbox.Calendar;

import android.app.Dialog;
import android.app.TimePickerDialog;
import android.os.Bundle;
import android.support.v4.app.DialogFragment;
import android.text.format.DateFormat;
import android.widget.TextView;
import android.widget.TimePicker;

import java.util.Calendar;

/**
 * Created by tan_f on 22/01/2016.
 */
public class TimePickerFragment extends DialogFragment
        implements TimePickerDialog.OnTimeSetListener {

    TextView _eventHour;

    @Override
    public Dialog onCreateDialog(Bundle savedInstanceState) {
        // Use the current time as the default values for the picker
        final Calendar c = Calendar.getInstance();
        int hour = c.get(Calendar.HOUR_OF_DAY);
        int minute = c.get(Calendar.MINUTE);

        // Create a new instance of TimePickerDialog and return it
        return new TimePickerDialog(getActivity(), this, hour, minute,
                DateFormat.is24HourFormat(getActivity()));
    }

    public void setTextViewHour(TextView textHour)
    {
        _eventHour = textHour;
    }

    public void onTimeSet(TimePicker view, int hourOfDay, int minute) {
        String eventHour;
        String eventMin;
        String date;

        if (hourOfDay < 10)
            eventHour = "0" + String.valueOf(hourOfDay);
        else
            eventHour = String.valueOf(hourOfDay);

        if (minute < 10)
            eventMin = "0" + String.valueOf(minute);
        else
            eventMin = String.valueOf(minute);
        date = eventHour + ":" + eventMin;
        _eventHour.setText(date);
    }
}
