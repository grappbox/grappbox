package com.grappbox.grappbox.data;

import android.content.ContentValues;
import android.database.Cursor;
import android.database.SQLException;
import android.database.sqlite.SQLiteDatabase;
import android.database.sqlite.SQLiteQueryBuilder;
import android.net.Uri;
import android.support.annotation.NonNull;

import com.grappbox.grappbox.data.GrappboxContract.TimelineEntry;
import com.grappbox.grappbox.data.GrappboxContract.TimelineMessageEntry;

/**
 * Created by marcw on 30/08/2016.
 */
public class TimelineMessageCursors {
    private static final SQLiteQueryBuilder sQueryBuilder;

    private static final String sTimelineIdSelection = TimelineEntry.TABLE_NAME + "." + TimelineEntry._ID + "=?";
    private static final String sTimelineGrappboxIdSelection = TimelineEntry.TABLE_NAME + "." + TimelineEntry.COLUMN_GRAPPBOX_ID;
    private static final String sIdSelection = TimelineMessageEntry.TABLE_NAME + "." + TimelineMessageEntry._ID + "=?";
    private static final String sGrappboxIdSelection = TimelineMessageEntry.TABLE_NAME + "." + TimelineMessageEntry.COLUMN_GRAPPBOX_ID + "=?";

    static {
        sQueryBuilder = new SQLiteQueryBuilder();
        sQueryBuilder.setTables(TimelineMessageEntry.TABLE_NAME + " INNER JOIN " + TimelineEntry.TABLE_NAME +
        " ON " + TimelineMessageEntry.TABLE_NAME + "." + TimelineMessageEntry.COLUMN_LOCAL_TIMELINE_ID +
        " = " + TimelineEntry.TABLE_NAME + "." + TimelineEntry._ID);
    }

    public static Cursor query_TimelineMessage(@NonNull Uri uri, String[] projection, String selection, String[] args, String sortOrder, GrappboxDBHelper openHelper){
        return sQueryBuilder.query(openHelper.getReadableDatabase(), projection, selection, args, null, null, sortOrder);
    }

    public static Cursor query_TimelineMessageByTimelineId(@NonNull Uri uri, String[] projection, String selection, String[] args, String sortOrder, GrappboxDBHelper openHelper){
        return sQueryBuilder.query(openHelper.getReadableDatabase(), projection, sTimelineIdSelection, new String[]{uri.getLastPathSegment()}, null, null, sortOrder);
    }

    public static Cursor query_TimelineMessageByGrappboxTimelineId(@NonNull Uri uri, String[] projection, String selection, String[] args, String sortOrder, GrappboxDBHelper openHelper){
        return sQueryBuilder.query(openHelper.getReadableDatabase(), projection, sTimelineGrappboxIdSelection, new String[]{uri.getLastPathSegment()}, null, null, sortOrder);
    }

    public static Cursor query_TimelineMessageById(@NonNull Uri uri, String[] projection, String selection, String[] args, String sortOrder, GrappboxDBHelper openHelper){
        return sQueryBuilder.query(openHelper.getReadableDatabase(), projection, sIdSelection, new String[]{uri.getLastPathSegment()}, null, null, sortOrder);
    }

    public static Cursor query_TimelineMessageByGrappboxId(@NonNull Uri uri, String[] projection, String selection, String[] args, String sortOrder, GrappboxDBHelper openHelper){
        return sQueryBuilder.query(openHelper.getReadableDatabase(), projection, sGrappboxIdSelection, new String[]{uri.getLastPathSegment()}, null, null, sortOrder);
    }

    public static Uri insert(@NonNull Uri uri, ContentValues values, GrappboxDBHelper openHelper){
        long id = openHelper.getWritableDatabase().insert(TimelineMessageEntry.TABLE_NAME, null, values);
        if (id <= 0)
            throw new SQLException("Failed to insert row into " + uri);
        return TimelineMessageEntry.buildTimelineMessageWithLocalIdUri(id);
    }

    public static int bulkInsert(@NonNull Uri uri, ContentValues[] values, GrappboxDBHelper openHelper) {
        final SQLiteDatabase db = openHelper.getWritableDatabase();
        int returnCount = 0;

        db.beginTransaction();
        try {
            for (ContentValues value : values) {
                long _id = db.insert(TimelineMessageEntry.TABLE_NAME, null, value);
                if (_id > 0)
                    ++returnCount;
            }
            db.setTransactionSuccessful();
        } finally {
            db.endTransaction();
        }
        return returnCount;
    }

    public static int update(Uri uri, ContentValues contentValues, String selection, String[] args, GrappboxDBHelper mOpenHelper) {
        return mOpenHelper.getWritableDatabase().update(TimelineMessageEntry.TABLE_NAME, contentValues, selection, args);
    }
}
