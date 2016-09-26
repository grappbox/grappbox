package com.grappbox.grappbox.data;

import android.content.ContentValues;
import android.database.Cursor;
import android.database.SQLException;
import android.database.sqlite.SQLiteDatabase;
import android.database.sqlite.SQLiteQueryBuilder;
import android.net.Uri;
import android.support.annotation.NonNull;
import com.grappbox.grappbox.data.GrappboxContract.BugAssignationEntry;
import com.grappbox.grappbox.data.GrappboxContract.BugEntry;
import com.grappbox.grappbox.data.GrappboxContract.UserEntry;

/**
 * Created by marcw on 30/08/2016.
 */
public class BugAssignationCursors {
    private static final SQLiteQueryBuilder sBugAssignationQueryBuilder;

    private static final String sGrappoxBugSelection = BugEntry.TABLE_NAME + "." + BugEntry.COLUMN_GRAPPBOX_ID + " = ?";
    private static final String sGrappboxUserSelection = UserEntry.TABLE_NAME + "." + UserEntry.COLUMN_GRAPPBOX_ID + " = ?";

    static{
        sBugAssignationQueryBuilder = new SQLiteQueryBuilder();
        sBugAssignationQueryBuilder.setTables(BugAssignationEntry.TABLE_NAME + " INNER JOIN " +
        BugEntry.TABLE_NAME + " ON " + BugAssignationEntry.TABLE_NAME + "." + BugAssignationEntry.COLUMN_LOCAL_BUG_ID +
        " = " + BugEntry.TABLE_NAME + "." + BugEntry._ID + " INNER JOIN " +
        UserEntry.TABLE_NAME + " ON " + BugAssignationEntry.TABLE_NAME + "." + BugAssignationEntry.COLUMN_LOCAL_USER_ID +
        " = " + UserEntry.TABLE_NAME + "." + UserEntry._ID);
    }

    public static Cursor query_BugAssignation(@NonNull Uri uri, String[] projection, String selection, String[] args, String sortOrder, GrappboxDBHelper openHelper){
        return openHelper.getReadableDatabase().query(BugAssignationEntry.TABLE_NAME, projection, selection, args, null, null, sortOrder);
    }

    public static Cursor query_BugAssignationByBugId(@NonNull Uri uri, String[] projection, String selection, String[] args, String sortOrder, GrappboxDBHelper openHelper){
        return sBugAssignationQueryBuilder.query(openHelper.getReadableDatabase(), projection, BugAssignationEntry.COLUMN_LOCAL_BUG_ID + "= ?", new String[]{uri.getLastPathSegment()}, null, null, sortOrder);
    }

    public static Cursor query_BugAssignationByGrappboxBugId(@NonNull Uri uri, String[] projection, String selection, String[] args, String sortOrder, GrappboxDBHelper openHelper){
        return sBugAssignationQueryBuilder.query(openHelper.getReadableDatabase(), projection, sGrappoxBugSelection, new String[]{uri.getLastPathSegment()}, null, null, sortOrder);
    }

    public static Cursor query_BugAssignationByUserId(@NonNull Uri uri, String[] projection, String selection, String[] args, String sortOrder, GrappboxDBHelper openHelper){
        return sBugAssignationQueryBuilder.query(openHelper.getReadableDatabase(), projection, BugAssignationEntry.COLUMN_LOCAL_USER_ID + "= ?", new String[]{uri.getLastPathSegment()}, null, null, sortOrder);
    }

    public static Cursor query_BugAssignationByGrappboxUserId(@NonNull Uri uri, String[] projection, String selection, String[] args, String sortOrder, GrappboxDBHelper openHelper){
        return sBugAssignationQueryBuilder.query(openHelper.getReadableDatabase(), projection, sGrappboxUserSelection, new String[]{uri.getLastPathSegment()}, null, null, sortOrder);
    }

    public static Uri insert(@NonNull Uri uri, ContentValues values, GrappboxDBHelper openHelper){
        Cursor result = openHelper.getReadableDatabase().query(BugAssignationEntry.TABLE_NAME, new String[]{BugEntry._ID}, BugAssignationEntry.COLUMN_LOCAL_BUG_ID + "=? AND " + BugAssignationEntry.COLUMN_LOCAL_USER_ID + "=?", new String[]{values.getAsString(BugAssignationEntry.COLUMN_LOCAL_BUG_ID), values.getAsString(BugAssignationEntry.COLUMN_LOCAL_USER_ID)}, null, null, null);
        if (result != null && result.moveToFirst()){
            long id = result.getLong(0);
            result.close();
            return GrappboxContract.BugTagEntry.buildBugTagWithLocalIdUri(id);
        }
        long id = openHelper.getWritableDatabase().insert(BugAssignationEntry.TABLE_NAME, null, values);
        if (id <= 0)
            throw new SQLException("Failed to insert row into " + uri);
        return GrappboxContract.BugAssignationEntry.buildBugAssignationWithLocalIdUri(id);
    }

    public static int bulkInsert(@NonNull Uri uri, ContentValues[] values, GrappboxDBHelper openHelper) {
        final SQLiteDatabase db = openHelper.getWritableDatabase();
        int returnCount = 0;

        db.beginTransaction();
        try {
            for (ContentValues value : values) {
                long _id = db.insert(BugAssignationEntry.TABLE_NAME, null, value);
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
        return mOpenHelper.getWritableDatabase().update(BugAssignationEntry.TABLE_NAME, contentValues, selection, args);
    }
}
