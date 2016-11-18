package com.grappbox.grappbox.data;

import android.content.ContentValues;
import android.database.Cursor;
import android.database.SQLException;
import android.database.sqlite.SQLiteDatabase;
import android.database.sqlite.SQLiteQueryBuilder;
import android.net.Uri;
import android.support.annotation.NonNull;

import com.grappbox.grappbox.data.GrappboxContract.EventEntry;
import com.grappbox.grappbox.data.GrappboxContract.EventParticipantEntry;
import com.grappbox.grappbox.data.GrappboxContract.UserEntry;

/**
 * Created by marcw on 30/08/2016.
 */
public class EventParticipantCursors {
    private static final SQLiteQueryBuilder sEventParticipantQueryBuilder;

    private static final String sEventIdSelection = EventEntry.TABLE_NAME + "." + EventEntry._ID + "=?";
    private static final String sEventGrappboxIdSelection = EventEntry.TABLE_NAME + "." + EventEntry.COLUMN_GRAPPBOX_ID + "=?";

    static {
        sEventParticipantQueryBuilder = new SQLiteQueryBuilder();
        sEventParticipantQueryBuilder.setTables(EventParticipantEntry.TABLE_NAME + " INNER JOIN " +
        EventEntry.TABLE_NAME + " ON " + EventParticipantEntry.TABLE_NAME + "." + EventParticipantEntry.COLUMN_LOCAL_EVENT_ID +
        " = " + EventEntry.TABLE_NAME + "." + EventEntry._ID + " INNER JOIN " +
        UserEntry.TABLE_NAME + " ON " + EventParticipantEntry.TABLE_NAME + "." + EventParticipantEntry.COLUMN_LOCAL_USER_ID +
        " = " + UserEntry.TABLE_NAME + "." + UserEntry._ID);
    }

    public static Cursor query_EventParticipant(@NonNull Uri uri, String[] projection, String selection, String[] args, String sortOrder, GrappboxDBHelper openHelper){
        return sEventParticipantQueryBuilder.query(openHelper.getReadableDatabase(), projection, selection, args, null, null, sortOrder);
    }

    public static Cursor query_EventParticipantByEventId(@NonNull Uri uri, String[] projection, String selection, String[] args, String sortOrder, GrappboxDBHelper openHelper){
        return sEventParticipantQueryBuilder.query(openHelper.getReadableDatabase(), projection, sEventIdSelection, new String[]{uri.getLastPathSegment()}, null, null, sortOrder);
    }

    public static Cursor query_EventParticipantByGrappboxEventId(@NonNull Uri uri, String[] projection, String selection, String[] args, String sortOrder, GrappboxDBHelper openHelper){
        return sEventParticipantQueryBuilder.query(openHelper.getReadableDatabase(), projection, sEventGrappboxIdSelection, new String[]{uri.getLastPathSegment()}, null, null, sortOrder);
    }

    public static Uri insert(@NonNull Uri uri, ContentValues values, GrappboxDBHelper openHelper){
        long id = openHelper.getWritableDatabase().insert(EventParticipantEntry.TABLE_NAME, null, values);
        if (id <= 0)
            throw new SQLException("Failed to insert row into " + uri);
        return EventEntry.buildEventWithLocalIdUri(id);
    }

    public static int bulkInsert(@NonNull Uri uri, ContentValues[] values, GrappboxDBHelper openHelper) {
        final SQLiteDatabase db = openHelper.getWritableDatabase();
        int returnCount = 0;

        db.beginTransaction();
        try {
            for (ContentValues value : values) {
                long _id = db.insert(EventParticipantEntry.TABLE_NAME, null, value);
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
        return mOpenHelper.getWritableDatabase().update(EventParticipantEntry.TABLE_NAME, contentValues, selection, args);
    }
}
