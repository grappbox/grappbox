package com.grappbox.grappbox;

import android.content.Context;

import java.text.SimpleDateFormat;
import java.util.Calendar;
import java.util.Locale;

/**
 * Created by tan_f on 01/11/2016.
 */

public class CalendarManager {
    private static CalendarManager mInstance = null;
    private static final String LOG_TAG = CalendarManager.class.getSimpleName();

    private Context mContext;
    private Locale mLocale;
    private Calendar mToday = Calendar.getInstance();
    private SimpleDateFormat mWeekDayFormatter;
    private SimpleDateFormat mMonthNameFormat;

    private CalendarManager(Context context) {
        mContext = context;
    }

    public static CalendarManager getInstace(Context context) {
        if (mInstance == null)
            mInstance = new CalendarManager(context);
        return mInstance;
    }

    public static CalendarManager getInstance() { return mInstance; }



}
