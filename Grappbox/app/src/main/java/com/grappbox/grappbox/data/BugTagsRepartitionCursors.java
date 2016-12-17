package com.grappbox.grappbox.data;

import android.content.ContentValues;
import android.database.Cursor;
import android.database.SQLException;
import android.database.sqlite.SQLiteDatabase;
import android.database.sqlite.SQLiteQueryBuilder;
import android.net.Uri;
import android.support.annotation.NonNull;

import com.grappbox.grappbox.data.GrappboxContract.BugTagsRepartitionEntry;
import com.grappbox.grappbox.data.GrappboxContract.StatEntry;
/**
 * Created by tan_f on 16/12/2016.
 */

public class BugTagsRepartitionCursors {

    private static final SQLiteQueryBuilder sBugTagsRepartitionWithStat;

    static {
        sBugTagsRepartitionWithStat = new SQLiteQueryBuilder();
        sBugTagsRepartitionWithStat.setTables(BugTagsRepartitionEntry.TABLE_NAME + " INNER JOIN " + StatEntry.TABLE_NAME +
        " ON " + BugTagsRepartitionEntry.TABLE_NAME + "." + BugTagsRepartitionEntry.COLUMN_LOCAL_STAT_ID + " = " + StatEntry.TABLE_NAME + "." + StatEntry._ID);
    }

    public static Cursor query_BugTagsRepartition(@NonNull Uri uri, String[] projection, String selection, String[] args, String sortOrder, GrappboxDBHelper openHelper) {
        return openHelper.getReadableDatabase().query(BugTagsRepartitionEntry.TABLE_NAME, projection, selection, args, null, null, sortOrder);
    }

    public static Cursor query_BugTagsRepartitionById(@NonNull Uri uri, String[] projection, String selection, String[] args, String sortOrder, GrappboxDBHelper openHelper) {
        return openHelper.getReadableDatabase().query(BugTagsRepartitionEntry.TABLE_NAME, projection, BugTagsRepartitionEntry._ID + "=?", new String[]{uri.getLastPathSegment()}, null, null, sortOrder);
    }
    public static Cursor query_BugTagsRepartitionByStat(@NonNull Uri uri, String[] projection, String selection, String[] args, String sortOrder, GrappboxDBHelper openHelper) {
        return sBugTagsRepartitionWithStat.query(openHelper.getReadableDatabase(), projection, selection, args, null, null, sortOrder);
    }

    public static Uri insert (@NonNull Uri uri, ContentValues values, GrappboxDBHelper openHelper) {
        long id = openHelper.getWritableDatabase().insert(BugTagsRepartitionEntry.TABLE_NAME, null, values);
        if (id <= 0)
            throw new SQLException("Failed to insert row into " + uri);
        return BugTagsRepartitionEntry.buildBugTagsRepartitionlocalUri(id);
    }

    public static int bulkInsert(@NonNull Uri uri, ContentValues[] values, GrappboxDBHelper openHelper) {
        final SQLiteDatabase db = openHelper.getWritableDatabase();
        int returnCount = 0;

        db.beginTransaction();
        try {
            for (ContentValues value : values) {
                long _id = db.insert(BugTagsRepartitionEntry.TABLE_NAME, null, value);
                if (_id > 0)
                    ++returnCount;
            }
        } finally {
            db.endTransaction();
        }
        return returnCount;
    }

    public static int update (Uri uri, ContentValues contentValues, String selection, String[] arsg, GrappboxDBHelper mOpenHelper) {
        return mOpenHelper.getWritableDatabase().update(BugTagsRepartitionEntry.TABLE_NAME, contentValues, selection, arsg);
    }
}
