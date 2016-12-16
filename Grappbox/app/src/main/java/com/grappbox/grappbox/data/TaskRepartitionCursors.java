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
import com.grappbox.grappbox.data.GrappboxContract.TaskRepartitionEntry;

/**
 * Created by tan_f on 16/12/2016.
 */

public class TaskRepartitionCursors {

    public static final SQLiteQueryBuilder sTaskRepartitionCompleteQueryBuilder;

    static {
        sTaskRepartitionCompleteQueryBuilder = new SQLiteQueryBuilder();
        sTaskRepartitionCompleteQueryBuilder.setTables(GrappboxContract.UserWorkingChargeEntry.TABLE_NAME + " INNER JOIN " + StatEntry.TABLE_NAME +
                " ON " + TaskRepartitionEntry.TABLE_NAME + "." + TaskRepartitionEntry.COLUMN_LOCAL_STAT_ID +
                " = " + StatEntry.TABLE_NAME + "." + StatEntry._ID + " INNER JOIN " + UserEntry.TABLE_NAME +
                " ON " + TaskRepartitionEntry.TABLE_NAME + "." + TaskRepartitionEntry.COLUMN_LOCAL_USER_ID +
                " = " + UserEntry.TABLE_NAME + "." + UserEntry._ID);
    }

    public static Cursor query_TaskRepartition(@NonNull Uri uri, String[] projection, String selection, String[] args, String sortOrder, GrappboxDBHelper openHelper){
        return openHelper.getReadableDatabase().query(TaskRepartitionEntry.TABLE_NAME, projection, selection, args, null, null, sortOrder);
    }

    public static Cursor query_TaskRepartitionById(@NonNull Uri uri, String[] projection, String selection, String[] args, String sortOrder, GrappboxDBHelper openHelper) {
        return openHelper.getReadableDatabase().query(TaskRepartitionEntry.TABLE_NAME, projection, TaskRepartitionEntry._ID + "=?", args, null, null, sortOrder);
    }

    public static Uri insert(@NonNull Uri uri, ContentValues values, GrappboxDBHelper openHelper) {
        long id = openHelper.getWritableDatabase().insert(TaskRepartitionEntry.TABLE_NAME, null, values);
        if (id <= 0)
            throw new SQLException("Failed to insert row into " + uri);
        return TaskRepartitionEntry.buildTaskRepartitionLocalUri(id);
    }

    public static int bulkInsert(@NonNull Uri uri, ContentValues[] values, GrappboxDBHelper openHelper) {
        final SQLiteDatabase db = openHelper.getWritableDatabase();
        int returnCount = 0;

        db.beginTransaction();
        try {
            for (ContentValues value : values) {
                long _id = db.insert(TaskRepartitionEntry.TABLE_NAME, null, value);
                if (_id > 0)
                    ++returnCount;
                db.setTransactionSuccessful();
            }
        } finally {
            db.endTransaction();
        }
        return returnCount;
    }

    public static Cursor query_TaskRepartitionWithStat(Uri uri, String[] projection, String selection, String[] args, String sortOrder, GrappboxDBHelper mOpenHelper) {
        return sTaskRepartitionCompleteQueryBuilder.query(mOpenHelper.getReadableDatabase(), projection, selection, args, null, null, sortOrder);
    }

    public static Cursor query_TaskRepartitionWithUser(Uri uri, String[] projection, String selection, String[] args, String sortOrder, GrappboxDBHelper mOpenHelper) {
        return sTaskRepartitionCompleteQueryBuilder.query(mOpenHelper.getReadableDatabase(), projection, selection, args, null, null, sortOrder);
    }

    public static int update(Uri uri, ContentValues values, String selection, String[] args, GrappboxDBHelper mOpenHelper) {
        return mOpenHelper.getWritableDatabase().update(TaskRepartitionEntry.TABLE_NAME, values, selection, args);
    }
}
