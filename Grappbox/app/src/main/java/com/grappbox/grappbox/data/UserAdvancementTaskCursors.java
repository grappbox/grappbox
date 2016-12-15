package com.grappbox.grappbox.data;

import android.content.ContentValues;
import android.database.Cursor;
import android.database.SQLException;
import android.database.sqlite.SQLiteDatabase;
import android.database.sqlite.SQLiteQueryBuilder;
import android.net.Uri;
import android.support.annotation.NonNull;

import com.grappbox.grappbox.data.GrappboxContract.UserAdvancementTaskEntry;
import com.grappbox.grappbox.data.GrappboxContract.StatEntry;
import com.grappbox.grappbox.data.GrappboxContract.UserEntry;

/**
 * Created by tan_f on 14/12/2016.
 */

public class UserAdvancementTaskCursors {

    private static final SQLiteQueryBuilder sUserAdvancementTaskQueryBuilder;

    static {
        sUserAdvancementTaskQueryBuilder = new SQLiteQueryBuilder();
        sUserAdvancementTaskQueryBuilder.setTables(UserAdvancementTaskEntry.TABLE_NAME + " INNER JOIN " + StatEntry.TABLE_NAME +
        " ON " + UserAdvancementTaskEntry.TABLE_NAME + "." + UserAdvancementTaskEntry.COLUMN_LOCAL_STAT_ID +
        " = " + StatEntry.TABLE_NAME + "." + StatEntry._ID + " INNER JOIN " + UserEntry.TABLE_NAME +
        " ON " + UserAdvancementTaskEntry.TABLE_NAME + "." + UserAdvancementTaskEntry.COLUMN_LOCAL_USER_ID +
        " = " + UserEntry.TABLE_NAME + "." + UserEntry._ID);
    }

    public static Cursor query_UserAdvancementTasl(@NonNull Uri uri, String[] projection, String selection, String[] args, String sortOrder, GrappboxDBHelper openHelper) {
        return openHelper.getReadableDatabase().query(UserAdvancementTaskEntry.TABLE_NAME, projection, selection, args, null, null, sortOrder);
    }

    public static Cursor query_AdvancementById(@NonNull Uri uri, String[] projection, String selection, String[] args, String sortOrder, GrappboxDBHelper openHelper){
        return openHelper.getReadableDatabase().query(UserAdvancementTaskEntry.TABLE_NAME, projection, UserAdvancementTaskEntry._ID + "=?", new String[]{uri.getLastPathSegment()}, null, null, sortOrder);
    }

    public static Uri insert(@NonNull Uri uri, ContentValues values, GrappboxDBHelper openHelper) {
        long id = openHelper.getWritableDatabase().insert(UserAdvancementTaskEntry.TABLE_NAME, null, values);
        if (id <= 0)
            throw new SQLException("Failed to insert row into " + uri);
        return GrappboxContract.AdvancementEntry.buildAdvancementWithLocalIdUri(id);
    }

    public static int bulkInsert(@NonNull Uri uri, ContentValues[] values, GrappboxDBHelper openHelper){
        final SQLiteDatabase db = openHelper.getWritableDatabase();
        int returnCount = 0;

        db.beginTransaction();
        try {
            for (ContentValues value : values) {
                long _id = db.insert(UserAdvancementTaskEntry.TABLE_NAME, null, value);
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
        return mOpenHelper.getWritableDatabase().update(UserAdvancementTaskEntry.TABLE_NAME, contentValues, selection, args);
    }
}
