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
import com.grappbox.grappbox.data.GrappboxContract.BugTagEntry;
import com.grappbox.grappbox.data.GrappboxContract.BugtrackerTagEntry;
import com.grappbox.grappbox.data.GrappboxContract.UserEntry;

/**
 * Created by marcw on 30/08/2016.
 */
public class BugCursors {
    private static final SQLiteQueryBuilder sCompleteQueryBuilder;
    private static final SQLiteQueryBuilder sBugWithAssignationBuilder;
    private static final SQLiteQueryBuilder sBugWithTag;
    private static final SQLiteQueryBuilder sBugWithCreator;


    static {
        sCompleteQueryBuilder = new SQLiteQueryBuilder();
        sCompleteQueryBuilder.setTables(BugEntry.TABLE_NAME + " INNER JOIN " + BugTagEntry.TABLE_NAME +
                " ON " + BugEntry.TABLE_NAME + "." + BugEntry._ID + " = " + BugTagEntry.TABLE_NAME + "." + BugTagEntry.COLUMN_LOCAL_BUG_ID +
                " INNER JOIN " + BugtrackerTagEntry.TABLE_NAME +
                " ON " + BugTagEntry.TABLE_NAME + "." + BugTagEntry.COLUMN_LOCAL_TAG_ID + " = " + BugtrackerTagEntry.TABLE_NAME + "." + BugtrackerTagEntry._ID +
                " INNER JOIN " + BugAssignationEntry.TABLE_NAME +
                " ON " + BugAssignationEntry.TABLE_NAME + "." + BugAssignationEntry.COLUMN_LOCAL_BUG_ID + " = " + BugEntry.TABLE_NAME + "." + BugEntry._ID +
                " INNER JOIN " + UserEntry.TABLE_NAME +
                " ON " + BugAssignationEntry.TABLE_NAME + "." + BugAssignationEntry.COLUMN_LOCAL_USER_ID + " = " + UserEntry.TABLE_NAME + "." + UserEntry._ID);

        sBugWithAssignationBuilder = new SQLiteQueryBuilder();
        sBugWithAssignationBuilder.setTables(BugEntry.TABLE_NAME + " INNER JOIN " + BugAssignationEntry.TABLE_NAME +
                " ON " + BugEntry.TABLE_NAME + "." + BugEntry._ID + " = " + BugAssignationEntry.TABLE_NAME + "." + BugAssignationEntry.COLUMN_LOCAL_BUG_ID);

        sBugWithTag = new SQLiteQueryBuilder();
        sBugWithTag.setTables(BugEntry.TABLE_NAME + " INNER JOIN " + BugTagEntry.TABLE_NAME +
                " ON " + BugEntry.TABLE_NAME + "." + BugEntry._ID + " = " + BugTagEntry.TABLE_NAME + "." + BugTagEntry.COLUMN_LOCAL_BUG_ID +
                " INNER JOIN " + BugtrackerTagEntry.TABLE_NAME +
                " ON " + BugTagEntry.TABLE_NAME + "." + BugTagEntry.COLUMN_LOCAL_TAG_ID + " = " + BugtrackerTagEntry.TABLE_NAME + "." + BugtrackerTagEntry._ID);

        sBugWithCreator = new SQLiteQueryBuilder();
        sBugWithCreator.setTables(BugEntry.TABLE_NAME + " INNER JOIN " + UserEntry.TABLE_NAME +
                " ON " + BugEntry.TABLE_NAME + "." + BugEntry.COLUMN_LOCAL_CREATOR_ID + " = " + UserEntry.TABLE_NAME + "." + UserEntry._ID);
    }


    public static Cursor query_Bug(@NonNull Uri uri, String[] projection, String selection, String[] args, String sortOrder, GrappboxDBHelper openHelper){
        return openHelper.getReadableDatabase().query(BugEntry.TABLE_NAME, projection, selection, args, null, null, sortOrder);
    }

    public static Cursor query_BugWithTag(@NonNull Uri uri, String[] projection, String selection, String[] args, String sortOrder, GrappboxDBHelper openHelper){
        return sBugWithTag.query(openHelper.getReadableDatabase(), projection, selection, args, null, null, sortOrder);
    }

    public static Cursor query_BugById(@NonNull Uri uri, String[] projection, String selection, String[] args, String sortOrder, GrappboxDBHelper openHelper){
        return openHelper.getReadableDatabase().query(BugEntry.TABLE_NAME, projection, BugEntry._ID + "=?", new String[]{uri.getLastPathSegment()}, null, null, sortOrder);
    }

    public static Cursor query_BugByGrappboxId(@NonNull Uri uri, String[] projection, String selection, String[] args, String sortOrder, GrappboxDBHelper openHelper){
        return openHelper.getReadableDatabase().query(BugEntry.TABLE_NAME, projection, BugEntry.COLUMN_GRAPPBOX_ID + "=?", new String[]{uri.getLastPathSegment()}, null, null, sortOrder);
    }

    public static Cursor query_BugWithTagAndAssignation(@NonNull Uri uri, String[] projection, String selection, String[] args, String sortOrder, GrappboxDBHelper openHelper){
        return sCompleteQueryBuilder.query(openHelper.getReadableDatabase(), projection, selection, args, null, null, sortOrder);
    }

    public static Cursor query_BugWithAssignation(@NonNull Uri uri, String[] projection, String selection, String[] args, String sortOrder, GrappboxDBHelper openHelper){
        return sBugWithAssignationBuilder.query(openHelper.getReadableDatabase(), projection, selection, args, null, null, sortOrder);
    }

    public static Uri insert(@NonNull Uri uri, ContentValues values, GrappboxDBHelper openHelper){
        long id = openHelper.getWritableDatabase().insert(BugEntry.TABLE_NAME, null, values);
        if (id <= 0)
            throw new SQLException("Failed to insert row into " + uri);
        return BugEntry.buildBugWithLocalIdUri(id);
    }

    public static int bulkInsert(@NonNull Uri uri, ContentValues[] values, GrappboxDBHelper openHelper) {
        final SQLiteDatabase db = openHelper.getWritableDatabase();
        int returnCount = 0;

        db.beginTransaction();
        try {
            for (ContentValues value : values) {
                long _id = db.insert(GrappboxContract.BugEntry.TABLE_NAME, null, value);
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
        return mOpenHelper.getWritableDatabase().update(BugEntry.TABLE_NAME, contentValues, selection, args);
    }

    public static Cursor query_BugWithCreator(Uri uri, String[] projection, String selection, String[] args, String sortOrder, GrappboxDBHelper mOpenHelper) {
        return sBugWithCreator.query(mOpenHelper.getReadableDatabase(), projection, selection, args, null, null, sortOrder);
    }
}
