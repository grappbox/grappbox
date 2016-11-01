package com.grappbox.grappbox.model;

import java.util.Calendar;
import java.util.Date;

/**
 * Created by tan_f on 01/11/2016.
 */

public class CalendarDayModel {
    private Date    mDate;
    private int     mValue;
    private int     mDayOfTheWeek;
    private boolean mToday;
    private boolean mFirstDayOfTheMonth;
    private boolean mSelected;
    private String  mMonth;


    public CalendarDayModel() {
    }

    public CalendarDayModel(Date date, int value, boolean today, String month){
        mDate = date;
        mValue = value;
        mToday = today;
        mMonth = month;
    }

    public CalendarDayModel(CalendarDayModel other){
        this.mDate = other.mDate;
        this.mValue = other.mValue;
        this.mDayOfTheWeek = other.mDayOfTheWeek;
        this.mToday = other.mToday;
        this.mFirstDayOfTheMonth = other.mFirstDayOfTheMonth;
        this.mSelected = other.mSelected;
        this.mMonth = other.mMonth;
    }

    public Date getDate() { return mDate; }

    public int getValue() { return mValue; }

    public int getDayOfTheWeek() { return mDayOfTheWeek; }

    public boolean isToday() { return mToday; }

    public boolean isFirstDayOfTheMonth() { return mFirstDayOfTheMonth; }

    public boolean isSelected() { return mSelected; }

    public String getMonth() { return mMonth; }

    public void setDate(Date date){ mDate = date; }

    public void setValue(int value) { mValue = value; }

    public void setDayOfTheWeek(int dayOfTheWeek) { mDayOfTheWeek = dayOfTheWeek; }

    public void setToday(boolean today) { mToday = today; }

    public void setFirstDayOfTheMonth(boolean firstDayOfTheMonth) { mFirstDayOfTheMonth = firstDayOfTheMonth; }

    public void setSelected(boolean selected) { mSelected = selected; }

    public void setMonth(String month) { mMonth = month; }

    public void buildDayItemFromCalendar(Calendar calendar){
        Date date = calendar.getTime();
        mDate = date;

        mValue = calendar.get(Calendar.DAY_OF_MONTH);
        mToday = sameDate(calendar, Calendar.getInstance());

        if (mValue == 1)
            mFirstDayOfTheMonth = true;
    }

    public boolean sameDate(Calendar cal, Calendar selectedDate) {
        return cal.get(Calendar.MONTH) == selectedDate.get(Calendar.MONTH)
                && cal.get(Calendar.YEAR) == selectedDate.get(Calendar.YEAR)
                && cal.get(Calendar.DAY_OF_MONTH) == selectedDate.get(Calendar.DAY_OF_MONTH);
    }

    @Override
    public String toString() {
        return "DayItem{Date='" + mDate.toString() + ", value=" + mValue + '}';
    }

    public CalendarDayModel copy() {return new CalendarDayModel(this); }
}
