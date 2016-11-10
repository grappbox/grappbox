package com.grappbox.grappbox.model;

import java.text.SimpleDateFormat;
import java.util.Calendar;
import java.util.Date;

/**
 * Created by tan_f on 10/11/2016.
 */

public class CalendarEventDateFormatModel {

    private Calendar _calendar;
    private SimpleDateFormat mFormat = new SimpleDateFormat("yyyy-MM-dd HH:mm");

    public CalendarEventDateFormatModel() {
        _calendar = Calendar.getInstance();
    }

    public Date getDate(){
        return  _calendar.getTime();
    }

    public void setDate(int year, int month, int dayOfMonth){
        _calendar.set(Calendar.YEAR, year);
        _calendar.set(Calendar.MONTH, month);
        _calendar.set(Calendar.DAY_OF_MONTH, dayOfMonth);
    }

    public void setHour(int hour, int minute){
        _calendar.set(Calendar.HOUR, hour);
        _calendar.set(Calendar.MINUTE, minute);
    }

    @Override
    public String toString() {
        Date date = _calendar.getTime();
        return mFormat.format(date);
    }
}
