package com.grappbox.grappbox.data;

import android.content.ContentValues;
import android.database.Cursor;
import android.database.SQLException;
import android.database.sqlite.SQLiteDatabase;
import android.database.sqlite.SQLiteQueryBuilder;
import android.net.Uri;
import android.support.annotation.NonNull;

import com.grappbox.grappbox.data.GrappboxContract.CloudEntry;
import com.grappbox.grappbox.data.GrappboxContract.ProjectEntry;

/**
 * Created by marcw on 30/08/2016.
 */
public class CloudCursors {
    private static final SQLiteQueryBuilder sProjectJoinQueryBuilder;

    static {
        sProjectJoinQueryBuilder = new SQLiteQueryBuilder();
        sProjectJoinQueryBuilder.setTables(CloudEntry.TABLE_NAME + " INNER JOIN " + ProjectEntry.TABLE_NAME +
        " ON " + CloudEntry.TABLE_NAME + "." + CloudEntry.COLUMN_LOCAL_PROJECT_ID + " = " + ProjectEntry.TABLE_NAME + "." + ProjectEntry._ID);
    }

    public static Cursor query_Cloud(@NonNull Uri uri, String[] projection, String selection, String[] args, String sortOrder, GrappboxDBHelper openHelper){
        return openHelper.getReadableDatabase().query(CloudEntry.TABLE_NAME, projection, selection, args, null, null, sortOrder);
    }

    public static Cursor query_CloudById(@NonNull Uri uri, String[] projection, String selection, String[] args, String sortOrder, GrappboxDBHelper openHelper){
        return openHelper.getReadableDatabase().query(CloudEntry.TABLE_NAME, projection, CloudEntry._ID + "=?", new String[]{uri.getLastPathSegment()}, null, null, sortOrder);
    }

    public static Uri insert(@NonNull Uri uri, ContentValues values, GrappboxDBHelper openHelper){
        long id = openHelper.getWritableDatabase().insert(CloudEntry.TABLE_NAME, null, values);
        if (id <= 0)
            throw new SQLException("Failed to insert row into " + uri);
        return CloudEntry.buildCloudWithLocalIdUri(id);
    }

    public static int bulkInsert(@NonNull Uri uri, ContentValues[] values, GrappboxDBHelper openHelper) {
        final SQLiteDatabase db = openHelper.getWritableDatabase();
        int returnCount = 0;

        db.beginTransaction();
        try {
            for (ContentValues value : values) {
                long _id = db.insert(CloudEntry.TABLE_NAME, null, value);
                if (_id > 0)
                    ++returnCount;
            }
            db.setTransactionSuccessful();
        } finally {
            db.endTransaction();
        }
        return returnCount;
    }

    public static Cursor query_CloudWithProject(Uri uri, String[] projection, String selection, String[] args, String sortOrder, GrappboxDBHelper mOpenHelper) {
        return sProjectJoinQueryBuilder.query(mOpenHelper.getReadableDatabase(), projection, selection, args, null, null, sortOrder);
    }
}
