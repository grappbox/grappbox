package com.grappbox.grappbox.calendar_fragment;

import android.content.Context;

import com.grappbox.grappbox.model.CalendarEventModel;

import java.util.ArrayList;
import java.util.Calendar;
import java.util.List;
import java.util.Locale;

/**
 * Created by tan_f on 26/10/2016.
 */

public class CalendarManager {

    private static final String LOG_TAG = CalendarManager.class.getSimpleName();

    private static CalendarManager mInstance;

    private Context mContext;
    private Locale mLocale;
    private Calendar mToday = Calendar.getInstance();

    private List<CalendarEventModel>    mEvents = new ArrayList<>();

    public CalendarManager(Context context) {
        mContext = context;
    }

    public static CalendarManager getInstance(Context context){
        if (mInstance == null)
            mInstance = new CalendarManager(context);
        return mInstance;
    }



}
