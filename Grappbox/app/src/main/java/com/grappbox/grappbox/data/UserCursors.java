package com.grappbox.grappbox.data;

import android.content.ContentValues;
import android.database.Cursor;
import android.database.sqlite.SQLiteDatabase;
import android.net.Uri;
import android.support.annotation.NonNull;
import android.support.annotation.Nullable;

import com.grappbox.grappbox.data.GrappboxContract.UserEntry;
import com.grappbox.grappbox.model.UserModel;

/**
 * Created by marcw on 30/08/2016.
 */
public class UserCursors {
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
