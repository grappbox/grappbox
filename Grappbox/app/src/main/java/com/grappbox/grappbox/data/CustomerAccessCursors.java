/*
 * Created by Marc Wieser the 4/11/2016
 * If you have any problem or question about this work
 * please contact the author at marc.wieser@gmail.com
 *
 * The following code is owned by GrappBox you can't
 * use it without any authorization or special instructions
 * GrappBox © 2016
 */

package com.grappbox.grappbox.data;


import android.content.ContentValues;
import android.database.Cursor;
import android.database.SQLException;
import android.database.sqlite.SQLiteDatabase;
import android.database.sqlite.SQLiteQueryBuilder;
import android.net.Uri;
import android.support.annotation.NonNull;

import com.grappbox.grappbox.data.GrappboxContract.CustomerAccessEntry;

public class CustomerAccessCursors {
    private static SQLiteQueryBuilder sWithProjectBuilder;

    static {
        sWithProjectBuilder = new SQLiteQueryBuilder();
        sWithProjectBuilder.setTables(CustomerAccessEntry.TABLE_NAME + " INNER JOIN " + GrappboxContract.ProjectEntry.TABLE_NAME +
        " ON " + CustomerAccessEntry.TABLE_NAME + "." + CustomerAccessEntry.COLUMN_PROJECT_ID + " = " + GrappboxContract.ProjectEntry.TABLE_NAME + "." + GrappboxContract.ProjectEntry._ID);
    }

    public static Cursor query(@NonNull Uri uri, String[] projection, String selection, String[] args, String sortOrder, GrappboxDBHelper openHelper){
        return openHelper.getReadableDatabase().query(CustomerAccessEntry.TABLE_NAME, projection, selection, args, null, null, sortOrder);
    }

    public static Cursor query_withProject(@NonNull Uri uri, String[] projection, String selection, String[] args, String sortOrder, GrappboxDBHelper openHelper){
        return sWithProjectBuilder.query(openHelper.getReadableDatabase(), projection, selection, args, null, null, sortOrder);
    }

    public static Uri insert(@NonNull Uri uri, ContentValues values, GrappboxDBHelper openHelper){
        long id = openHelper.getWritableDatabase().insert(CustomerAccessEntry.TABLE_NAME, null, values);
        if (id <= 0)
            throw new SQLException("Failed to insert row into " + uri);
        return CustomerAccessEntry.buildTimelineMessageWithLocalIdUri(id);
    }

    public static int bulkInsert(@NonNull Uri uri, ContentValues[] values, GrappboxDBHelper openHelper) {
        final SQLiteDatabase db = openHelper.getWritableDatabase();
        int returnCount = 0;

        db.beginTransaction();
        try {
            for (ContentValues value : values) {
                long _id = db.insert(CustomerAccessEntry.TABLE_NAME, null, value);
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
        return mOpenHelper.getWritableDatabase().update(CustomerAccessEntry.TABLE_NAME, contentValues, selection, args);
    }
}
