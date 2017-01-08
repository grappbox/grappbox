package com.grappbox.grappbox.data;

import android.content.ContentValues;
import android.database.Cursor;
import android.database.sqlite.SQLiteDatabase;
import android.database.sqlite.SQLiteQueryBuilder;
import android.net.Uri;
import android.support.annotation.NonNull;
import android.support.annotation.Nullable;

import com.grappbox.grappbox.data.GrappboxContract.NextMeetingEntry;
import com.grappbox.grappbox.data.GrappboxContract.ProjectEntry;

/**
 * Created by Arka on 08/01/2017.
 */

public class NextMeetingCursor {

    private static final SQLiteQueryBuilder sNextMeetingQueryBuilder;

    static {
        sNextMeetingQueryBuilder = new SQLiteQueryBuilder();

        sNextMeetingQueryBuilder.setTables(NextMeetingEntry.TABLE_NAME + " INNER JOIN " + ProjectEntry.TABLE_NAME +
        " ON " + NextMeetingEntry.TABLE_NAME + "." + NextMeetingEntry.COLUMN_LOCAL_PROJECT_ID +
        " = " + ProjectEntry.TABLE_NAME + "." + ProjectEntry._ID);
    }

    public static Cursor query_NextMeeting (@NonNull Uri uri, String[] projection, String selection, String[] args, String sortOrder, GrappboxDBHelper openHelper) {
        return openHelper.getReadableDatabase().query(NextMeetingEntry.TABLE_NAME, projection, selection, args, null, null, sortOrder);
    }

    public static Cursor query_NextMeetingById(@NonNull Uri uri, String[] projection, String selection, String[] args, String sortOrder, GrappboxDBHelper openHelper) {
        return openHelper.getReadableDatabase().query(NextMeetingEntry.TABLE_NAME, projection, NextMeetingEntry._ID + "=?", new String[]{uri.getLastPathSegment()}, null, null, sortOrder);
    }

    public static Cursor query_NextMeetingByProject(@NonNull Uri uri, String[] projection, String selection, String[] args, String sortOrder, GrappboxDBHelper openHelper) {
        return sNextMeetingQueryBuilder.query(openHelper.getReadableDatabase(), projection, selection, args, null, null, sortOrder);
    }

    public static Uri insert(@Nullable Uri uri, ContentValues contentValues, GrappboxDBHelper openHelper)
    {
        long id = openHelper.getWritableDatabase().insert(NextMeetingEntry.TABLE_NAME, null, contentValues);
        if (id <= 0)
            throw new android.database.SQLException("Failed to insert row into " + uri);
        return GrappboxContract.OccupationEntry.buildOccupationWithLocalIdUri(id);
    }

    public static int update(@NonNull Uri uri, ContentValues contentValues, String selection, String[] args, GrappboxDBHelper openHelper) {
        return openHelper.getWritableDatabase().update(NextMeetingEntry.TABLE_NAME, contentValues, selection, args);
    }

    public static int bulkInsert(Uri uri, ContentValues[] values, GrappboxDBHelper openHelper) {
        final SQLiteDatabase db = openHelper.getWritableDatabase();
        int returnCount = 0;

        db.beginTransaction();
        try {
            for (ContentValues value : values) {
                long _id = db.insert(NextMeetingEntry.TABLE_NAME, null, value);
                if (_id > 0)
                    ++returnCount;
                db.setTransactionSuccessful();
            }
        } finally {
            db.endTransaction();
        }
        return returnCount;
    }
}
