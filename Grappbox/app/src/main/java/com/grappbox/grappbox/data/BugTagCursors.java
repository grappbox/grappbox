package com.grappbox.grappbox.data;

import android.content.ContentValues;
import android.database.Cursor;
import android.database.SQLException;
import android.database.sqlite.SQLiteDatabase;
import android.database.sqlite.SQLiteQueryBuilder;
import android.net.Uri;
import android.support.annotation.NonNull;

import com.grappbox.grappbox.data.GrappboxContract.BugEntry;
import com.grappbox.grappbox.data.GrappboxContract.BugTagEntry;
import com.grappbox.grappbox.data.GrappboxContract.TagEntry;

/**
 * Created by marcw on 02/09/2016.
 */
public class BugTagCursors {
    private static SQLiteQueryBuilder sQueryBuilder;

    private static String sGrappboxBugIdSelection = BugEntry.TABLE_NAME + "." + BugEntry.COLUMN_GRAPPBOX_ID + "=?";

    static {
        sQueryBuilder = new SQLiteQueryBuilder();
        sQueryBuilder.setTables(BugTagEntry.TABLE_NAME + " INNER JOIN " + BugEntry.TABLE_NAME +
        " ON " + BugTagEntry.TABLE_NAME + "." + BugTagEntry.COLUMN_LOCAL_BUG_ID + " = " + BugEntry.TABLE_NAME + "." + BugEntry._ID +
        " INNER JOIN " + TagEntry.TABLE_NAME +
        " ON " + BugTagEntry.TABLE_NAME + "." + BugTagEntry.COLUMN_LOCAL_TAG_ID + " = " + TagEntry.TABLE_NAME + "." + TagEntry._ID);
    }

    public static Cursor query_BugTag(@NonNull Uri uri, String[] projection, String selection, String[] args, String sortOrder, GrappboxDBHelper openHelper) {
        return openHelper.getReadableDatabase().query(BugTagEntry.TABLE_NAME, projection, selection, args, null, null, sortOrder);
    }

    public static Cursor query_BugTagByGrappboxBugId(@NonNull Uri uri, String[] projection, String selection, String[] args, String sortOrder, GrappboxDBHelper openHelper){
        return sQueryBuilder.query(openHelper.getReadableDatabase(), projection, sGrappboxBugIdSelection, new String[] {uri.getLastPathSegment()}, null, null, sortOrder);
    }

    public static Uri insert(@NonNull Uri uri, ContentValues values, GrappboxDBHelper openHelper){
        Cursor result = openHelper.getReadableDatabase().query(BugTagEntry.TABLE_NAME, new String[]{BugEntry._ID}, BugTagEntry.COLUMN_LOCAL_BUG_ID + "=? AND " + BugTagEntry.COLUMN_LOCAL_TAG_ID + "=?", new String[]{values.getAsString(BugTagEntry.COLUMN_LOCAL_BUG_ID), values.getAsString(BugTagEntry.COLUMN_LOCAL_TAG_ID)}, null, null, null);
        if (result != null && result.moveToFirst()){
            return BugTagEntry.buildBugTagWithLocalIdUri(result.getLong(0));
        }
        long id = openHelper.getWritableDatabase().insert(BugTagEntry.TABLE_NAME, null, values);
        if (id <= 0)
            throw new SQLException("Failed to insert row into " + uri);
        return BugTagEntry.buildBugTagWithLocalIdUri(id);
    }

    public static int bulkInsert(@NonNull Uri uri, ContentValues[] values, GrappboxDBHelper openHelper) {
        final SQLiteDatabase db = openHelper.getWritableDatabase();
        int returnCount = 0;

        db.beginTransaction();
        try {
            for (ContentValues value : values) {
                long _id = db.insert(BugTagEntry.TABLE_NAME, null, value);
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
        return mOpenHelper.getWritableDatabase().update(BugTagEntry.TABLE_NAME, contentValues, selection, args);
    }
}
