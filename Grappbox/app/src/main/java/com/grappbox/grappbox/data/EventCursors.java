package com.grappbox.grappbox.data;

import android.content.ContentValues;
import android.database.Cursor;
import android.database.SQLException;
import android.database.sqlite.SQLiteDatabase;
import android.database.sqlite.SQLiteQueryBuilder;
import android.net.Uri;
import android.support.annotation.NonNull;

import com.grappbox.grappbox.data.GrappboxContract.EventEntry;
import com.grappbox.grappbox.data.GrappboxContract.EventTypeEntry;
import com.grappbox.grappbox.data.GrappboxContract.ProjectEntry;
import com.grappbox.grappbox.data.GrappboxContract.UserEntry;

/**
 * Created by marcw on 30/08/2016.
 */
public class EventCursors {
    private static final SQLiteQueryBuilder sEventWithTypeQueryBuilder;

    private static final String sEventByIdSelection = EventEntry.TABLE_NAME + "." + EventEntry._ID + "=?";
    private static final String sEventByGrappboxIdSelection = EventEntry.TABLE_NAME + "." + EventEntry.COLUMN_GRAPPBOX_ID + "=?";
    private static final String sEventByTypeIdSelection = EventTypeEntry.TABLE_NAME + "." + EventTypeEntry._ID + "=?";

    static {
        sEventWithTypeQueryBuilder = new SQLiteQueryBuilder();
        sEventWithTypeQueryBuilder.setTables(EventEntry.TABLE_NAME + " INNER JOIN " +
        EventTypeEntry.TABLE_NAME + " ON " + EventEntry.TABLE_NAME + "." + EventEntry.COLUMN_LOCAL_EVENT_TYPE_ID +
        " = " + EventTypeEntry.TABLE_NAME + "." + EventTypeEntry._ID + " INNER JOIN " +
        ProjectEntry.TABLE_NAME + " ON " + EventEntry.TABLE_NAME + "." + EventEntry.COLUMN_LOCAL_PROJECT_ID +
        " = " + ProjectEntry.TABLE_NAME + "." + ProjectEntry._ID + " INNER JOIN " +
        UserEntry.TABLE_NAME + " ON " + EventEntry.TABLE_NAME + "." + EventEntry.COLUMN_LOCAL_CREATOR_ID +
        " = " + UserEntry.TABLE_NAME + "." + UserEntry._ID);
    }

    public static Cursor query_Event(@NonNull Uri uri, String[] projection, String selection, String[] args, String sortOrder, GrappboxDBHelper openHelper){
        return sEventWithTypeQueryBuilder.query(openHelper.getReadableDatabase(), projection, selection, args, null, null, sortOrder);
    }

    public static Cursor query_EventById(@NonNull Uri uri, String[] projection, String selection, String[] args, String sortOrder, GrappboxDBHelper openHelper){
        return sEventWithTypeQueryBuilder.query(openHelper.getReadableDatabase(), projection, sEventByIdSelection, new String[]{uri.getLastPathSegment()}, null, null, sortOrder);
    }

    public static Cursor query_EventByGrappboxId(@NonNull Uri uri, String[] projection, String selection, String[] args, String sortOrder, GrappboxDBHelper openHelper){
        return sEventWithTypeQueryBuilder.query(openHelper.getReadableDatabase(), projection, sEventByGrappboxIdSelection, new String[]{uri.getLastPathSegment()}, null, null, sortOrder);
    }

    public static Cursor query_EventByTypeId(@NonNull Uri uri, String[] projection, String selection, String[] args, String sortOrder, GrappboxDBHelper openHelper){
        return sEventWithTypeQueryBuilder.query(openHelper.getReadableDatabase(), projection, sEventByTypeIdSelection, new String[]{uri.getLastPathSegment()}, null, null, sortOrder);
    }

    public static Uri insert(@NonNull Uri uri, ContentValues values, GrappboxDBHelper openHelper){
        long id = openHelper.getWritableDatabase().insert(EventEntry.TABLE_NAME, null, values);
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
                long _id = db.insert(EventEntry.TABLE_NAME, null, value);
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
