package com.grappbox.grappbox.grappbox.Calendar;

import android.app.DatePickerDialog;
import android.app.Dialog;
import android.os.Bundle;
import android.support.v4.app.DialogFragment;
import android.widget.DatePicker;
import android.widget.TextView;

import java.util.Calendar;

/**
 * Created by tan_f on 22/01/2016.
 */
public class DatePickerFragment extends DialogFragment
        implements DatePickerDialog.OnDateSetListener {

    TextView _textDate;

    @Override
    public Dialog onCreateDialog(Bundle savedInstanceState) {
        // Use the current date as the default date in the picker
        final Calendar c = Calendar.getInstance();
        int year = c.get(Calendar.YEAR);
        int month = c.get(Calendar.MONTH);
        int day = c.get(Calendar.DAY_OF_MONTH);

        // Create a new instance of DatePickerDialog and return it
        return new DatePickerDialog(getActivity(), this, year, month, day);
    }

    public void setTextView(TextView text)
    {
        _textDate = text;
    }

    public void onDateSet(DatePicker view, int year, int month, int day) {
        String monthDate;
        String dayDate;

        if (month < 10)
            monthDate = "0" + String.valueOf(month + 1);
        else
            monthDate = String.valueOf(month + 1);

        if (day < 10)
            dayDate = "0" + String.valueOf(day);
        else
            dayDate = String.valueOf(day);
        String date = String.valueOf(year) + "-" + monthDate + "-" + dayDate;
        _textDate.setText(date);
    }
}
