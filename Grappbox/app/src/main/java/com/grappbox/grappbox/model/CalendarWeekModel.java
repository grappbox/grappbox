package com.grappbox.grappbox.model;

import com.grappbox.grappbox.Utils;

import java.util.Date;
import java.util.List;

/**
 * Created by tan_f on 01/11/2016.
 */

public class CalendarWeekModel {

    private int mWeekYear;
    private int mYear;
    private int mMonth;
    private Date mDate;
    private String  mLabel;
    private List<CalendarDayModel> mDays;

    public CalendarWeekModel(int weekYear, int year, Date date, String label, int month) {
        mWeekYear = weekYear;
        mYear = year;
        mMonth = month;
        mDate = date;
        mLabel = label;
    }

    public CalendarWeekModel(CalendarWeekModel other){
        mWeekYear = other.mWeekYear;
        mYear = other.mYear;
        mMonth = other.mMonth;
        mDate = other.mDate;
        mLabel = other.mLabel;
    }

    public CalendarWeekModel(){
    }

    public int getWeekYear() { return mWeekYear; }

    public int getYear() { return mYear; }

    public int getMonth() { return mMonth; }

    public Date getDate() { return mDate; }

    public List<CalendarDayModel> getDays() { return mDays; }

    public String getLabel() { return mLabel; }

    public void setWeekYear(int weekYear) { mWeekYear = weekYear; }

    public void setYear(int year) { mYear = year; }

    public void setMonth(int month) { mMonth = month; }

    public void setDate(Date date) { mDate = date; }

    public void setLabel(String label) { mLabel = label; }

    public void setDays(List<CalendarDayModel> days) { mDays = days; }

    public CalendarWeekModel copy() { return new CalendarWeekModel(this); }

    @Override
    public String toString() {
        return "WeekItem{ label='" + mLabel + '\'' + ", weekInYear=" + mWeekYear + ", year=" + mYear + '}';
    }
}
