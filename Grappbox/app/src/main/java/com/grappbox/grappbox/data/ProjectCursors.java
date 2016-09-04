package com.grappbox.grappbox.data;

import android.content.ContentValues;
import android.database.Cursor;
import android.database.sqlite.SQLiteDatabase;
import android.database.sqlite.SQLiteQueryBuilder;
import android.net.Uri;
import android.support.annotation.NonNull;
import android.support.annotation.Nullable;

import com.grappbox.grappbox.data.GrappboxContract.ProjectEntry;
import com.grappbox.grappbox.data.GrappboxContract.RolesAssignationEntry;
import com.grappbox.grappbox.data.GrappboxContract.RolesEntry;
import com.grappbox.grappbox.data.GrappboxContract.UserEntry;

/**
 * Created by marcw on 30/08/2016.
 */
public class ProjectCursors {
    private static final SQLiteQueryBuilder sProjectUserQueryBuilder;

    static {
        sProjectUserQueryBuilder = new SQLiteQueryBuilder();
        sProjectUserQueryBuilder.setTables(ProjectEntry.TABLE_NAME + " INNER JOIN " + RolesEntry.TABLE_NAME +
        " ON " + ProjectEntry.TABLE_NAME + "." + ProjectEntry._ID +
        " = " + RolesEntry.TABLE_NAME + "." + RolesEntry.COLUMN_LOCAL_PROJECT_ID + " INNER JOIN " + RolesAssignationEntry.TABLE_NAME +
         " ON " + RolesEntry.TABLE_NAME + "." + RolesEntry._ID +
        " = " + RolesAssignationEntry.TABLE_NAME + "." + RolesAssignationEntry.COLUMN_LOCAL_ROLE_ID + " INNER JOIN " + UserEntry.TABLE_NAME +
        " ON " + RolesAssignationEntry.TABLE_NAME + "." + RolesAssignationEntry.COLUMN_LOCAL_USER_ID +
        " = " + UserEntry.TABLE_NAME + "." + UserEntry._ID);
    }


    public static Cursor query_Project(@NonNull Uri uri, String[] projection, String selection, String[] args, String sortOrder, GrappboxDBHelper openHelper){
        return openHelper.getReadableDatabase().query(ProjectEntry.TABLE_NAME, projection, selection, args, null, null,sortOrder);
    }

    public static Cursor query_ProjectWithUser(@NonNull Uri uri, String[] projection, String selection, String[] args, String sortOrder, GrappboxDBHelper openHelper){
        return sProjectUserQueryBuilder.query(openHelper.getReadableDatabase(), projection, selection, args, null, null, sortOrder);
    }

    public static Cursor query_ProjectOneById(@NonNull Uri uri, String[] projection, String selection, String[] args, String sortOrder, GrappboxDBHelper openHelper){
        return openHelper.getReadableDatabase().query(ProjectEntry.TABLE_NAME, projection, ProjectEntry._ID + "=?", new String[]{uri.getLastPathSegment()}, null, null, sortOrder);
    }

    public static Cursor query_ProjectOneByGrappboxId(@NonNull Uri uri, String[] projection, String selection, String[] args, String sortOrder, GrappboxDBHelper openHelper){
        return openHelper.getReadableDatabase().query(ProjectEntry.TABLE_NAME, projection, ProjectEntry.COLUMN_GRAPPBOX_ID + "=?", new String[]{uri.getLastPathSegment()}, null, null, sortOrder);
    }

    public static Uri insert(@NonNull Uri uri, ContentValues contentValues, GrappboxDBHelper openHelper)
    {
        long id = openHelper.getWritableDatabase().insert(ProjectEntry.TABLE_NAME, null, contentValues);
        if (id <= 0)
            throw new android.database.SQLException("Failed to insert row into " + uri);
        return ProjectEntry.buildProjectWithLocalIdUri(id);
    }

    public static int bulkInsert(@NonNull Uri uri, ContentValues[] values, GrappboxDBHelper openHelper)
    {
        final SQLiteDatabase db = openHelper.getWritableDatabase();
        int returnCount = 0;

        db.beginTransaction();
        try {
            for (ContentValues value : values) {
                long _id = db.insert(ProjectEntry.TABLE_NAME, null, value);
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
