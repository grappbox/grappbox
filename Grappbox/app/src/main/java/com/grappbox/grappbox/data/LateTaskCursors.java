package com.grappbox.grappbox.data;

import android.content.ContentValues;
import android.database.Cursor;
import android.database.SQLException;
import android.database.sqlite.SQLiteDatabase;
import android.database.sqlite.SQLiteQueryBuilder;
import android.net.Uri;
import android.support.annotation.NonNull;

import com.grappbox.grappbox.data.GrappboxContract.LateTaskEntry;
import com.grappbox.grappbox.data.GrappboxContract.UserEntry;
import com.grappbox.grappbox.data.GrappboxContract.StatEntry;
/**
 * Created by tan_f on 15/12/2016.
 */

public class LateTaskCursors {

    private static final SQLiteQueryBuilder sCompleteQueryBuilder;

    static {
        sCompleteQueryBuilder = new SQLiteQueryBuilder();
        sCompleteQueryBuilder.setTables(LateTaskEntry.TABLE_NAME + " INNER JOIN " + LateTaskEntry.TABLE_NAME +
                " ON " + LateTaskEntry.TABLE_NAME + "." + LateTaskEntry.COLUMN_LOCAL_STAT_ID + " = " + StatEntry.TABLE_NAME + "." + StatEntry._ID +
                " INNER JOIN " + UserEntry.TABLE_NAME +
                " ON " + UserEntry.TABLE_NAME + "." + UserEntry._ID + " = " + LateTaskEntry.TABLE_NAME + "." + LateTaskEntry.COLUMN_LOCAL_USER_ID);
    }

    public static Cursor query_LateTask(@NonNull Uri uri, String[] projection, String selection, String[] args, String sortOrder, GrappboxDBHelper openHelper){
        return openHelper.getReadableDatabase().query(LateTaskEntry.TABLE_NAME, projection, selection, args, null, null, sortOrder);
    }

    public static Uri insert(@NonNull Uri uri, ContentValues values, GrappboxDBHelper openHelper){
        long id = openHelper.getWritableDatabase().insert(LateTaskEntry.TABLE_NAME, null, values);
        if (id <= 0)
            throw new SQLException("Failed to insert row into " + uri);
        return GrappboxContract.LateTaskEntry.buildLateTaskWithLocalUri(id);
    }

    public static int bulkInsert(@NonNull Uri uri, ContentValues[] values, GrappboxDBHelper openHelper) {
        final SQLiteDatabase db = openHelper.getWritableDatabase();
        int returnCount = 0;

        db.beginTransaction();
        try {
            for (ContentValues value : values) {
                long _id = db.insert(LateTaskEntry.TABLE_NAME, null, value);
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
        return mOpenHelper.getWritableDatabase().update(LateTaskEntry.TABLE_NAME, contentValues, selection, args);
    }
}
