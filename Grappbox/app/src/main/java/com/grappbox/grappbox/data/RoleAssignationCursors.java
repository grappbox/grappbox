package com.grappbox.grappbox.data;

import android.content.ContentValues;
import android.database.Cursor;
import android.database.sqlite.SQLiteDatabase;
import android.database.sqlite.SQLiteQueryBuilder;
import android.net.Uri;
import android.support.annotation.NonNull;

import com.grappbox.grappbox.data.GrappboxContract.ProjectEntry;
import com.grappbox.grappbox.data.GrappboxContract.RolesEntry;
import com.grappbox.grappbox.data.GrappboxContract.RolesAssignationEntry;
import com.grappbox.grappbox.data.GrappboxContract.UserEntry;

import java.util.List;

/**
 * Created by marcw on 30/08/2016.
 */
public class RoleAssignationCursors {
    private static final SQLiteQueryBuilder sQueryBuilder;
    private static final SQLiteQueryBuilder sUserProjectQueryBuilder;

    private static final String sRoleIdSelection = RolesEntry.TABLE_NAME + "." + RolesEntry._ID + "=?";
    private static final String sGrappboxRoleIdSelection = RolesEntry.TABLE_NAME + "." + RolesEntry.COLUMN_GRAPPBOX_ID + "=?";
    private static final String sUserIdSelection = UserEntry.TABLE_NAME + "." + UserEntry._ID + "=?";
    private static final String sGrappboxUserIdSelection = UserEntry.TABLE_NAME + "." + UserEntry.COLUMN_GRAPPBOX_ID + "=?";
    private static final String sUserIdAndProjectIdSelection = UserEntry.TABLE_NAME + "." + UserEntry._ID + "=? AND " + ProjectEntry.TABLE_NAME + "." + ProjectEntry._ID + "=?";

    static {
        sQueryBuilder = new SQLiteQueryBuilder();
        sQueryBuilder.setTables(RolesAssignationEntry.TABLE_NAME + " INNER JOIN " + RolesEntry.TABLE_NAME +
        " ON " + RolesAssignationEntry.TABLE_NAME + "." + RolesAssignationEntry.COLUMN_LOCAL_ROLE_ID +
        " = " + RolesEntry.TABLE_NAME + "." + RolesEntry._ID + " INNER JOIN " + UserEntry.TABLE_NAME +
        " ON " + RolesAssignationEntry.TABLE_NAME + "." + RolesAssignationEntry.COLUMN_LOCAL_USER_ID +
        " = " + UserEntry.TABLE_NAME + "." + UserEntry._ID);

        sUserProjectQueryBuilder = new SQLiteQueryBuilder();
        sUserProjectQueryBuilder.setTables(RolesAssignationEntry.TABLE_NAME + " INNER JOIN " + RolesEntry.TABLE_NAME +
        " ON " + RolesAssignationEntry.TABLE_NAME + "." + RolesAssignationEntry.COLUMN_LOCAL_ROLE_ID +
        " = " + RolesEntry.TABLE_NAME + "." + RolesEntry._ID + " INNER JOIN " + UserEntry.TABLE_NAME +
        " ON " + RolesAssignationEntry.TABLE_NAME + "." + RolesAssignationEntry.COLUMN_LOCAL_USER_ID +
        " = " + UserEntry.TABLE_NAME + "." + UserEntry._ID + " INNER JOIN " + ProjectEntry.TABLE_NAME +
        " ON " + RolesEntry.TABLE_NAME + "." + RolesEntry.COLUMN_LOCAL_PROJECT_ID +
        " = " + ProjectEntry.TABLE_NAME + "." + ProjectEntry._ID);
    }

    public static Cursor query_RoleAssignation(@NonNull Uri uri, String[] projection, String selection, String[] args, String sortOrder, GrappboxDBHelper openHelper){
        return sQueryBuilder.query(openHelper.getReadableDatabase(), projection, selection, args, null, null, sortOrder);
    }

    public static Cursor query_RoleAssignationByRoleId(@NonNull Uri uri, String[] projection, String selection, String[] args, String sortOrder, GrappboxDBHelper openHelper){
        return sQueryBuilder.query(openHelper.getReadableDatabase(), projection, sRoleIdSelection, new String[]{uri.getLastPathSegment()}, null, null, sortOrder);
    }

    public static Cursor query_RoleAssignationByGrappboxRoleId(@NonNull Uri uri, String[] projection, String selection, String[] args, String sortOrder, GrappboxDBHelper openHelper){
        return sQueryBuilder.query(openHelper.getReadableDatabase(), projection, sGrappboxRoleIdSelection, new String[]{uri.getLastPathSegment()}, null, null, sortOrder);
    }

    public static Cursor query_RoleAssignationByUserId(@NonNull Uri uri, String[] projection, String selection, String[] args, String sortOrder, GrappboxDBHelper openHelper){
        return sQueryBuilder.query(openHelper.getReadableDatabase(), projection, sUserIdSelection, new String[]{uri.getLastPathSegment()}, null, null, sortOrder);
    }

    public static Cursor query_RoleAssignationByGrappboxUserId(@NonNull Uri uri, String[] projection, String selection, String[] args, String sortOrder, GrappboxDBHelper openHelper){
        return sQueryBuilder.query(openHelper.getReadableDatabase(), projection, sGrappboxUserIdSelection, new String[]{uri.getLastPathSegment()}, null, null, sortOrder);
    }

    public static Cursor query_RoleAssignationByUserIdAndProjectId(@NonNull Uri uri, String[] projection, String selection, String[] args, String sortOrder, GrappboxDBHelper openHelper) {
        List<String> pathSegments = uri.getPathSegments();
        String[] queryArgs = new String[] {
                pathSegments.get(pathSegments.size() - 2),
                pathSegments.get(pathSegments.size() - 1),
        };
        return sUserProjectQueryBuilder.query(openHelper.getReadableDatabase(), projection, sUserIdAndProjectIdSelection, queryArgs, null, null, sortOrder);
    }

    public static Uri insert(@NonNull Uri uri, ContentValues contentValues, GrappboxDBHelper openHelper)
    {
        long id = openHelper.getWritableDatabase().insert(RolesAssignationEntry.TABLE_NAME, null, contentValues);
        if (id <= 0)
            throw new android.database.SQLException("Failed to insert row into " + uri);
        return RolesAssignationEntry.buildRoleAssignationWithLocalIdUri(id);
    }

    public static int bulkInsert(@NonNull Uri uri, ContentValues[] values, GrappboxDBHelper openHelper)
    {
        final SQLiteDatabase db = openHelper.getWritableDatabase();
        int returnCount = 0;

        db.beginTransaction();
        try {
            for (ContentValues value : values) {
                long _id = db.insert(RolesAssignationEntry.TABLE_NAME, null, value);
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
        return mOpenHelper.getWritableDatabase().update(RolesAssignationEntry.TABLE_NAME, contentValues, selection, args);
    }
}
