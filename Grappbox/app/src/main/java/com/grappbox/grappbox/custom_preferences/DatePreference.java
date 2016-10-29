/*
 * Created by Marc Wieser on 28/10/2016
 * If you have any problem or question about this work
 * please contact the author at marc.wieser@gmail.com
 *
 * The following code is owned by GrappBox you can't
 * use it without any authorization or special instructions
 * GrappBox © 2016
 */

package com.grappbox.grappbox.custom_preferences;

import android.app.AlertDialog;
import android.content.Context;
import android.content.DialogInterface;
import android.preference.DialogPreference;
import android.provider.CalendarContract;
import android.util.AttributeSet;
import android.view.View;
import android.widget.DatePicker;

import com.grappbox.grappbox.R;
import com.grappbox.grappbox.Utils;

import java.text.ParseException;
import java.text.SimpleDateFormat;
import java.util.Calendar;
import java.util.Date;
import java.util.Locale;


public class DatePreference extends DialogPreference {
    DatePicker picker;
    int day, month, year;

    public DatePreference(Context context, AttributeSet attrs) {
        super(context, attrs);
        setDialogTitle("");
    }

    @Override
    protected View onCreateDialogView() {
        picker = new DatePicker(getContext());
        try {
            SimpleDateFormat formatter = new SimpleDateFormat("yyyy-MM-dd", Locale.getDefault());
            Date date = formatter.parse(getSummary().toString());
            Calendar cal = Calendar.getInstance();
            cal.setTime(date);
            picker.updateDate(cal.get(Calendar.YEAR), cal.get(Calendar.MONTH), cal.get(Calendar.DAY_OF_MONTH));
        } catch (ParseException e) {
            e.printStackTrace();
        }
        return picker;
    }

    @Override
    protected void onPrepareDialogBuilder(AlertDialog.Builder builder) {
        super.onPrepareDialogBuilder(builder);
    }

    @Override
    protected void onDialogClosed(boolean positiveResult) {
        if (positiveResult){
            day = picker.getDayOfMonth();
            month = picker.getMonth();
            year = picker.getYear();
            callChangeListener(getValue());
        } else{
            super.onDialogClosed(false);
        }
    }

    public String getValue(){
        Calendar dateCreator = Calendar.getInstance();
        dateCreator.set(Calendar.DAY_OF_MONTH, day);
        dateCreator.set(Calendar.MONTH, month);
        dateCreator.set(Calendar.YEAR, year);
        return Utils.Date.grappboxFormatter.format(dateCreator.getTime()).split(" ")[0];
    }
}
