package com.grappbox.grappbox.data;

import android.content.ContentValues;
import android.database.Cursor;
import android.database.SQLException;
import android.database.sqlite.SQLiteDatabase;
import android.database.sqlite.SQLiteQueryBuilder;
import android.net.Uri;
import android.support.annotation.NonNull;

import com.grappbox.grappbox.data.GrappboxContract.StatEntry;
import com.grappbox.grappbox.data.GrappboxContract.UserEntry;
import com.grappbox.grappbox.data.GrappboxContract.UserWorkingChargeEntry;

/**
 * Created by tan_f on 16/12/2016.
 */

public class UserWorkingChargeCursor {

    public static final SQLiteQueryBuilder sUserWorkingChargeCompleteQueryBuilder;

    static {
        sUserWorkingChargeCompleteQueryBuilder = new SQLiteQueryBuilder();
        sUserWorkingChargeCompleteQueryBuilder.setTables(UserWorkingChargeEntry.TABLE_NAME + " INNER JOIN " + StatEntry.TABLE_NAME +
                " ON " + UserWorkingChargeEntry.TABLE_NAME + "." + UserWorkingChargeEntry.COLUMN_LOCAL_STAT_ID +
                " = " + StatEntry.TABLE_NAME + "." + StatEntry._ID + " INNER JOIN " + UserEntry.TABLE_NAME +
                " ON " + UserWorkingChargeEntry.TABLE_NAME + "." + UserWorkingChargeEntry.COLUMN_LOCAL_USER_ID +
                " = " + UserEntry.TABLE_NAME + "." + UserEntry._ID);
    }

    public static Cursor query_UserWorkingChargeTask(@NonNull Uri uri, String[] projection, String selection, String[] args, String sortOrder, GrappboxDBHelper openHelper){
        return openHelper.getReadableDatabase().query(UserWorkingChargeEntry.TABLE_NAME, projection, selection, args, null, null, sortOrder);
    }

    public static Cursor query_UserWorkingChargeById(@NonNull Uri uri, String[] projection, String selection, String[] args, String sortOrder, GrappboxDBHelper openHelper) {
        return openHelper.getReadableDatabase().query(UserWorkingChargeEntry.TABLE_NAME, projection, UserWorkingChargeEntry._ID + "=?", args, null, null, sortOrder);
    }

    public static Uri insert(@NonNull Uri uri, ContentValues values, GrappboxDBHelper openHelper) {
        long id = openHelper.getWritableDatabase().insert(UserWorkingChargeEntry.TABLE_NAME, null, values);
        if (id <= 0)
            throw new SQLException("Failed to insert row into " + uri);
        return UserWorkingChargeEntry.buildUserWorkingChargeLocalUri(id);
    }

    public static int builInsert(@NonNull Uri uri, ContentValues[] values, GrappboxDBHelper openHelper) {
        final SQLiteDatabase db = openHelper.getWritableDatabase();
        int returnCount = 0;

        db.beginTransaction();
        try {
            for (ContentValues value : values) {
                long _id = db.insert(UserWorkingChargeEntry.TABLE_NAME, null, value);
                if (_id > 0)
                    ++returnCount;
                db.setTransactionSuccessful();
            }
        } finally {
            db.endTransaction();
        }
        return returnCount;
    }

    public static int update(Uri uri, ContentValues values, String selection, String[] args, GrappboxDBHelper mOpenHelper) {
        return mOpenHelper.getWritableDatabase().update(UserWorkingChargeEntry.TABLE_NAME, values, selection, args);
    }
}
