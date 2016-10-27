package com.grappbox.grappbox.data;

import android.content.ContentValues;
import android.database.Cursor;
import android.database.sqlite.SQLiteDatabase;
import android.database.sqlite.SQLiteQueryBuilder;
import android.net.Uri;
import android.support.annotation.NonNull;
import android.support.annotation.Nullable;
import android.util.Log;

import com.grappbox.grappbox.data.GrappboxContract.UserEntry;

import java.util.Arrays;

/**
 * Created by marcw on 30/08/2016.
 */
public class UserCursors {
    private static SQLiteQueryBuilder sUserWithProjectQueryBuilder;

    static {
        sUserWithProjectQueryBuilder = new SQLiteQueryBuilder();
        sUserWithProjectQueryBuilder.setTables(UserEntry.TABLE_NAME + " INNER JOIN " + GrappboxContract.RolesAssignationEntry.TABLE_NAME +
        " ON " + UserEntry.TABLE_NAME + "." + UserEntry._ID + " = " + GrappboxContract.RolesAssignationEntry.TABLE_NAME + "." + GrappboxContract.RolesAssignationEntry.COLUMN_LOCAL_USER_ID +
        " INNER JOIN " + GrappboxContract.RolesEntry.TABLE_NAME +
        " ON " + GrappboxContract.RolesAssignationEntry.TABLE_NAME + "." + GrappboxContract.RolesAssignationEntry.COLUMN_LOCAL_ROLE_ID + " = " + GrappboxContract.RolesEntry.TABLE_NAME + "." + GrappboxContract.RolesEntry._ID +
        " INNER JOIN " + GrappboxContract.ProjectEntry.TABLE_NAME +
        " ON " + GrappboxContract.ProjectEntry.TABLE_NAME + "." + GrappboxContract.ProjectEntry._ID + " = " + GrappboxContract.RolesEntry.TABLE_NAME + "." + GrappboxContract.RolesEntry.COLUMN_LOCAL_PROJECT_ID);
    }

    public static Cursor query_User(@NonNull Uri uri, String[] projection, String selection, String[] args, String sortOrder, GrappboxDBHelper openHelper){
        return openHelper.getReadableDatabase().query(UserEntry.TABLE_NAME, projection, selection, args, null, null, sortOrder);
    }

    public static Cursor query_UserById(@NonNull Uri uri, String[] projection, String selection, String[] args, String sortOrder, GrappboxDBHelper openHelper){
        return openHelper.getReadableDatabase().query(UserEntry.TABLE_NAME, projection, UserEntry._ID + "=?", new String[]{uri.getLastPathSegment()}, null, null, sortOrder);
    }

    public static Cursor query_UserByGrappboxId(@NonNull Uri uri, String[] projection, String selection, String[] args, String sortOrder, GrappboxDBHelper openHelper){
        return openHelper.getReadableDatabase().query(UserEntry.TABLE_NAME, projection, UserEntry.COLUMN_GRAPPBOX_ID + "=?", new String[]{uri.getLastPathSegment()}, null, null, sortOrder);
    }

    public static Cursor query_UserByEmail(@NonNull Uri uri, String[] projection, String selection, String[] args, String sortOrder, GrappboxDBHelper openHelper){
        return openHelper.getReadableDatabase().query(UserEntry.TABLE_NAME, projection, UserEntry.COLUMN_CONTACT_EMAIL + "=?", new String[]{uri.getLastPathSegment()}, null, null, sortOrder);
    }

    public static int update(@NonNull Uri uri, ContentValues contentValues, String selection, String[] args, GrappboxDBHelper openHelper) {
        return openHelper.getWritableDatabase().update(UserEntry.TABLE_NAME, contentValues, selection, args);
    }

    public static Cursor query_UserWithProject(@NonNull Uri url, String[] projection, String selection, String[] args, String sort, GrappboxDBHelper openHelper){
        return sUserWithProjectQueryBuilder.query(openHelper.getReadableDatabase(), projection, selection, args, null, null, sort);
    }

    public static int updateWithId(@NonNull Uri uri, ContentValues contentValues, String selection, String[] args, GrappboxDBHelper openHelper) {
        return openHelper.getWritableDatabase().update(UserEntry.TABLE_NAME, contentValues, UserEntry._ID + "=?", new String[] {uri.getLastPathSegment()});
    }

    public static Uri insert(@Nullable Uri uri, ContentValues contentValues, GrappboxDBHelper openHelper)
    {
        long id = openHelper.getWritableDatabase().insert(UserEntry.TABLE_NAME, null, contentValues);
        if (id <= 0)
            throw new android.database.SQLException("Failed to insert row into " + uri);
        return UserEntry.buildUserWithLocalIdUri(id);
    }

    public static int bulkInsert(Uri uri, ContentValues[] values, GrappboxDBHelper openHelper) {
        final SQLiteDatabase db = openHelper.getWritableDatabase();
        int returnCount = 0;

        db.beginTransaction();
        try {
            for (ContentValues value : values) {
                long _id = db.insert(UserEntry.TABLE_NAME, null, value);
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
