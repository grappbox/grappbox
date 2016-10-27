package com.grappbox.grappbox.model;

import android.database.Cursor;

import java.util.Calendar;

/**
 * Created by tan_f on 26/10/2016.
 */

public class CalendarEventModel {
    private long        _id;
    private long        _projectId;
    private int         _color;
    private String      _title;
    private String      _description;
    private Calendar    _beginDate;
    private Calendar    _endDate;

    public CalendarEventModel(Cursor cursor){

    }

}
