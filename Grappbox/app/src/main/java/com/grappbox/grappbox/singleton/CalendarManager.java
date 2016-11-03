package com.grappbox.grappbox.singleton;

import android.content.Context;

import com.grappbox.grappbox.model.CalendarDayModel;
import com.grappbox.grappbox.model.CalendarEventModel;
import com.grappbox.grappbox.model.CalendarWeekModel;

import java.text.SimpleDateFormat;
import java.util.ArrayList;
import java.util.Calendar;
import java.util.Date;
import java.util.List;
import java.util.Locale;

/**
 * Created by tan_f on 02/11/2016.
 */

public class CalendarManager {

    private static final String LOG_TAG = CalendarManager.class.getSimpleName();
    private static CalendarManager mInstance = null;

    private Context     mContext;
    private Locale      mLocale;
    private Calendar    mToday = Calendar.getInstance();
    private SimpleDateFormat    mWeekDayFormat;
    private SimpleDateFormat    mMonthNameFormat;

    private CalendarDayModel    mCleanDay;
    private CalendarWeekModel   mCleanWeek;

    private List<CalendarDayModel>      mDays = new ArrayList<>();
    private List<CalendarWeekModel>     mWeeks = new ArrayList<>();
    private List<CalendarEventModel>    mEvents = new ArrayList<>();

    public static CalendarManager getInstance(Context context){
        if (mInstance == null) {
            mInstance = new CalendarManager(context);
        }
        return mInstance;
    }

    public static CalendarManager getInstance(){
        return mInstance;
    }

    private CalendarManager(Context context) {
        mContext = context;
    }

    public Locale getLocale() { return mLocale; }

    public Context getContext() { return mContext; }

    public Calendar getToday() { return mToday; }

    public void setToday(Calendar today) {
        mToday = today;
    }

    public List<CalendarDayModel> getDays() { return mDays; }

    public List<CalendarWeekModel> getWeeks() { return mWeeks; }

    public List<CalendarEventModel> getEvents() { return mEvents; }

    public SimpleDateFormat getWeekDayFormatter() { return mWeekDayFormat; }

    public SimpleDateFormat getMongthNameFormatter() { return mMonthNameFormat; }

    public void buildCalendar(Calendar minDate, Calendar maxDate, Locale locale, CalendarDayModel cleanDay, CalendarWeekModel cleanWeek) {
        if (minDate == null || maxDate == null)
            throw new IllegalArgumentException("min date and max date must be non-null");
        if (minDate.after(maxDate))
            throw new IllegalArgumentException("min date must be before max date");
        if (locale == null)
            throw new IllegalArgumentException("locale must be non-null");
        setLocale(locale);
        mDays.clear();
        mWeeks.clear();
        mEvents.clear();
        mCleanDay = cleanDay;
        mCleanWeek = cleanWeek;

        Calendar calendarMin = Calendar.getInstance(mLocale);
        Calendar calendarMax = Calendar.getInstance(mLocale);
        Calendar weekCounter = Calendar.getInstance(mLocale);

        calendarMin.setTime(minDate.getTime());
        calendarMax.setTime(maxDate.getTime());

        calendarMax.add(Calendar.MINUTE, -1);

        int maxMonth = calendarMax.get(Calendar.MONTH);
        int maxYear = calendarMax.get(Calendar.YEAR);

        int currentMonth = weekCounter.get(Calendar.MONTH);
        int currentYear = weekCounter.get(Calendar.YEAR);

        while ((currentMonth <= maxMonth || currentYear <= maxYear) && currentYear < maxYear + 1)
        {
            Date date = weekCounter.getTime();
            int currentWeekOfYear = weekCounter.get(Calendar.WEEK_OF_YEAR);

            CalendarWeekModel weekModel = cleanWeek.copy();
            weekModel.setWeekYear(currentWeekOfYear);
            weekModel.setYear(currentYear);
            weekModel.setDate(date);
            weekModel.setMonth(currentMonth);
            weekModel.setLabel(mMonthNameFormat.format(date));
            List<CalendarDayModel> dayModels = getDays(weekCounter);
            weekModel.setDays(dayModels);

            weekCounter.add(Calendar.WEEK_OF_YEAR, 1);

            currentMonth = weekCounter.get(Calendar.MONTH);
            currentYear = weekCounter.get(Calendar.YEAR);
        }
    }

    private List<CalendarDayModel> getDays(Calendar startCalendar){
        Calendar calendar = Calendar.getInstance(mLocale);
        calendar.setTime(startCalendar.getTime());
        List<CalendarDayModel> dayModels = new ArrayList<>();
        int firstDayOfTheWeek = calendar.get(Calendar.DAY_OF_WEEK);
        int offset = calendar.getFirstDayOfWeek() - firstDayOfTheWeek;
        if (offset > 0)
            offset -= 7;
        calendar.add(Calendar.DATE, offset);

        for (int i = 0; i < 7; ++i){
            CalendarDayModel day = mCleanDay.copy();
            day.buildDayItemFromCalendar(calendar);
            dayModels.add(day);
            calendar.add(Calendar.DATE, 1);
        }
        mDays.addAll(dayModels);
        return dayModels;
    }

    private void setLocale(Locale locale) {
        mLocale = locale;
        setToday(Calendar.getInstance(mLocale));
        mWeekDayFormat = new SimpleDateFormat("EEEEE", mLocale);
        mMonthNameFormat = new SimpleDateFormat("MMMM", mLocale);
    }
}
