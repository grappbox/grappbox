package com.grappbox.grappbox.data;

import android.content.ContentValues;
import android.database.Cursor;
import android.database.SQLException;
import android.database.sqlite.SQLiteDatabase;
import android.database.sqlite.SQLiteQueryBuilder;
import android.net.Uri;
import android.support.annotation.NonNull;

import com.grappbox.grappbox.data.GrappboxContract.AdvancementEntry;
import com.grappbox.grappbox.data.GrappboxContract.StatEntry;

/**
 * Created by tan_f on 14/12/2016.
 */

public class AdvancementCursors {
    private static final SQLiteQueryBuilder sStatJoinQueryBuilder;

    static {
        sStatJoinQueryBuilder = new SQLiteQueryBuilder();
        sStatJoinQueryBuilder.setTables(AdvancementEntry.TABLE_NAME + " INNER JOIN " + StatEntry.TABLE_NAME +
        " ON " + AdvancementEntry.TABLE_NAME + "." + AdvancementEntry.COLUMN_LOCAL_STATS_ID + " = " + StatEntry.TABLE_NAME + "." + StatEntry._ID);
    }

    public static Cursor query_Advancement(@NonNull Uri uri, String[] projection, String selection, String[] args, String sortOrder, GrappboxDBHelper openHelper){
        return openHelper.getReadableDatabase().query(AdvancementEntry.TABLE_NAME, projection, selection, args, null, null, sortOrder);
    }

    public static Cursor query_AdvancementById(@NonNull Uri uri, String[] projection, String selection, String[] args, String sortOrder, GrappboxDBHelper openHelper){
        return openHelper.getReadableDatabase().query(AdvancementEntry.TABLE_NAME, projection, AdvancementEntry._ID + "=?", new String[]{uri.getLastPathSegment()}, null, null, sortOrder);
    }

    public static Uri insert(@NonNull Uri uri, ContentValues values, GrappboxDBHelper openHelper) {
        long id = openHelper.getWritableDatabase().insert(AdvancementEntry.TABLE_NAME, null, values);
        if (id <= 0)
            throw new SQLException("Failed to insert row into " + uri);
        return AdvancementEntry.buildAdvancementWithLocalIdUri(id);
    }

    public static int bulkInsert(@NonNull Uri uri, ContentValues[] values, GrappboxDBHelper openHelper){
        final SQLiteDatabase db = openHelper.getWritableDatabase();
        int returnCount = 0;

        db.beginTransaction();
        try {
            for (ContentValues value : values) {
                long _id = db.insert(AdvancementEntry.TABLE_NAME, null, value);
                if (_id > 0)
                    ++returnCount;
            }
            db.setTransactionSuccessful();
        } finally {
            db.endTransaction();
        }
        return returnCount;
    }

    public static Cursor query_AdvancementWithStat(Uri uri, String[] projection, String selection, String[] args, String sortOrder, GrappboxDBHelper mOpenHelper) {
        return sStatJoinQueryBuilder.query(mOpenHelper.getReadableDatabase(), projection, selection, args, null, null, sortOrder);
    }

    public static int update(Uri uri, ContentValues contentValues, String selection, String[] args, GrappboxDBHelper mOpenHelper) {
        return mOpenHelper.getWritableDatabase().update(AdvancementEntry.TABLE_NAME, contentValues, selection, args);
    }
}
