package com.grappbox.grappbox.data;

import android.content.ContentValues;
import android.database.Cursor;
import android.database.SQLException;
import android.database.sqlite.SQLiteDatabase;
import android.database.sqlite.SQLiteQueryBuilder;
import android.net.Uri;
import android.support.annotation.NonNull;

import com.grappbox.grappbox.data.GrappboxContract.ProjectEntry;
import com.grappbox.grappbox.data.GrappboxContract.TimelineEntry;

/**
 * Created by marcw on 30/08/2016.
 */
public class TimelineCursors {
    private static final SQLiteQueryBuilder sQueryBuilder;

    private static final String sProjectIdSelection = ProjectEntry.TABLE_NAME + "." + ProjectEntry._ID;
    private static final String sGrappboxProjectIdSelection = ProjectEntry.TABLE_NAME + "." + ProjectEntry.COLUMN_GRAPPBOX_ID;
    private static final String sIdSelection = TimelineEntry.TABLE_NAME + "." + TimelineEntry._ID;
    private static final String sGrappboxIdSelection = TimelineEntry.TABLE_NAME + "." + TimelineEntry._ID;

    static {
        sQueryBuilder = new SQLiteQueryBuilder();
        sQueryBuilder.setTables(TimelineEntry.TABLE_NAME + " INNER JOIN " + ProjectEntry.TABLE_NAME +
        " ON " + TimelineEntry.TABLE_NAME + "." + TimelineEntry.COLUMN_LOCAL_PROJECT_ID +
        " = " + ProjectEntry.TABLE_NAME + "." + ProjectEntry._ID);
    }

    public static Cursor query_Timeline(@NonNull Uri uri, String[] projection, String selection, String[] args, String sortOrder, GrappboxDBHelper openHelper){
        return sQueryBuilder.query(openHelper.getReadableDatabase(), projection, selection, args, null, null, sortOrder);
    }

    public static Cursor query_TimelineByProjectId(@NonNull Uri uri, String[] projection, String selection, String[] args, String sortOrder, GrappboxDBHelper openHelper){
        return sQueryBuilder.query(openHelper.getReadableDatabase(), projection, sProjectIdSelection, new String[]{uri.getLastPathSegment()}, null, null, sortOrder);
    }

    public static Cursor query_TimelineByGrappboxProjectId(@NonNull Uri uri, String[] projection, String selection, String[] args, String sortOrder, GrappboxDBHelper openHelper){
        return sQueryBuilder.query(openHelper.getReadableDatabase(), projection, sGrappboxProjectIdSelection, new String[]{uri.getLastPathSegment()}, null, null, sortOrder);
    }

    public static Cursor query_TimelineByGrappboxId(@NonNull Uri uri, String[] projection, String selection, String[] args, String sortOrder, GrappboxDBHelper openHelper){
        return sQueryBuilder.query(openHelper.getReadableDatabase(), projection, sGrappboxIdSelection, new String[]{uri.getLastPathSegment()}, null, null, sortOrder);
    }

    public static Cursor query_TimelineById(@NonNull Uri uri, String[] projection, String selection, String[] args, String sortOrder, GrappboxDBHelper openHelper){
        return sQueryBuilder.query(openHelper.getReadableDatabase(), projection, sIdSelection, new String[]{uri.getLastPathSegment()}, null, null, sortOrder);
    }

    public static Uri insert(@NonNull Uri uri, ContentValues values, GrappboxDBHelper openHelper){
        long id = openHelper.getWritableDatabase().insert(TimelineEntry.TABLE_NAME, null, values);
        if (id <= 0)
            throw new SQLException("Failed to insert row into " + uri);
        return TimelineEntry.buildTimelineWithLocalIdUri(id);
    }

    public static int bulkInsert(@NonNull Uri uri, ContentValues[] values, GrappboxDBHelper openHelper) {
        final SQLiteDatabase db = openHelper.getWritableDatabase();
        int returnCount = 0;

        db.beginTransaction();
        try {
            for (ContentValues value : values) {
                long _id = db.insert(TimelineEntry.TABLE_NAME, null, value);
                if (_id > 0)
                    ++returnCount;
            }
            db.setTransactionSuccessful();
        } finally {
            db.endTransaction();
        }
        return returnCount;
    }
}
