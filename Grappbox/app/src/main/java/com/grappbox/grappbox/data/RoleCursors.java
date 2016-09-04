package com.grappbox.grappbox.data;

import android.content.ContentValues;
import android.database.Cursor;
import android.database.sqlite.SQLiteDatabase;
import android.database.sqlite.SQLiteQueryBuilder;
import android.net.Uri;
import android.support.annotation.NonNull;

import com.grappbox.grappbox.data.GrappboxContract.ProjectEntry;
import com.grappbox.grappbox.data.GrappboxContract.RolesEntry;

/**
 * Created by marcw on 30/08/2016.
 */
public class RoleCursors {
    private static final SQLiteQueryBuilder sQueryBuilder;

    private static final String sGrappboxRoleIdSelection = RolesEntry.TABLE_NAME + "." + RolesEntry.COLUMN_GRAPPBOX_ID + "=?";
    private static final String sRoleIdSelection = RolesEntry.TABLE_NAME + "." + RolesEntry._ID + "=?";
    private static final String sGrappboxProjectIdSelection = ProjectEntry.TABLE_NAME + "." + ProjectEntry.COLUMN_GRAPPBOX_ID + "=?";
    private static final String sProjectIdSelection = ProjectEntry.TABLE_NAME + "." + ProjectEntry._ID + "=?";

    static {
        sQueryBuilder = new SQLiteQueryBuilder();
        sQueryBuilder.setTables(RolesEntry.TABLE_NAME + " INNER JOIN " + ProjectEntry.TABLE_NAME +
        " ON " + RolesEntry.TABLE_NAME + "." + RolesEntry.COLUMN_LOCAL_PROJECT_ID +
        " = " + ProjectEntry.TABLE_NAME + "." + ProjectEntry._ID);
    }

    public static Cursor query_Role(@NonNull Uri uri, String[] projection, String selection, String[] args, String sortOrder, GrappboxDBHelper openHelper){
        return sQueryBuilder.query(openHelper.getReadableDatabase(), projection, selection, args, null, null, sortOrder);
    }

    public static Cursor query_RoleByGrappboxId(@NonNull Uri uri, String[] projection, String selection, String[] args, String sortOrder, GrappboxDBHelper openHelper){
        return sQueryBuilder.query(openHelper.getReadableDatabase(), projection, sGrappboxRoleIdSelection, new String[] { uri.getLastPathSegment() }, null, null, sortOrder);
    }

    public static Cursor query_RoleById(@NonNull Uri uri, String[] projection, String selection, String[] args, String sortOrder, GrappboxDBHelper openHelper){
        return sQueryBuilder.query(openHelper.getReadableDatabase(), projection, sRoleIdSelection, new String[] { uri.getLastPathSegment() }, null, null, sortOrder);
    }

    public static Cursor query_RoleByProjectId(@NonNull Uri uri, String[] projection, String selection, String[] args, String sortOrder, GrappboxDBHelper openHelper){
        return sQueryBuilder.query(openHelper.getReadableDatabase(), projection, sProjectIdSelection, new String[] { uri.getLastPathSegment() }, null, null, sortOrder);
    }

    public static Cursor query_RoleByGrappboxProjectId(@NonNull Uri uri, String[] projection, String selection, String[] args, String sortOrder, GrappboxDBHelper openHelper){
        return sQueryBuilder.query(openHelper.getReadableDatabase(), projection, sGrappboxProjectIdSelection, new String[] { uri.getLastPathSegment() }, null, null, sortOrder);
    }

    public static Uri insert(@NonNull Uri uri, ContentValues contentValues, GrappboxDBHelper openHelper)
    {
        long id = openHelper.getWritableDatabase().insert(RolesEntry.TABLE_NAME, null, contentValues);
        if (id <= 0)
            throw new android.database.SQLException("Failed to insert row into " + uri);
        return RolesEntry.buildRoleWithLocalIdUri(id);
    }

    public static int bulkInsert(@NonNull Uri uri, ContentValues[] values, GrappboxDBHelper openHelper)
    {
        final SQLiteDatabase db = openHelper.getWritableDatabase();
        int returnCount = 0;

        db.beginTransaction();
        try {
            for (ContentValues value : values) {
                long _id = db.insert(RolesEntry.TABLE_NAME, null, value);
                if (_id > 0)
                    ++returnCount;
            }
            db.setTransactionSuccessful();
        } finally {
            db.endTransaction();
        }
        return returnCount;
    }
}
