package com.grappbox.grappbox.data;

import android.content.ContentValues;
import android.database.Cursor;
import android.database.sqlite.SQLiteDatabase;
import android.database.sqlite.SQLiteQueryBuilder;
import android.net.Uri;
import android.support.annotation.NonNull;
import android.support.annotation.Nullable;

import com.grappbox.grappbox.data.GrappboxContract.OccupationEntry;
import com.grappbox.grappbox.data.GrappboxContract.ProjectEntry;
import com.grappbox.grappbox.data.GrappboxContract.UserEntry;

/**
 * Created by marcw on 30/08/2016.
 */
public class OccupationCursors {
    private static final SQLiteQueryBuilder sOccupationQueryBuilder;

    private static final String sProjectIdSelection = ProjectEntry.TABLE_NAME + "." + ProjectEntry._ID + "=?";
    private static final String sGrappboxProjectIdSelection = ProjectEntry.TABLE_NAME + "." + ProjectEntry.COLUMN_GRAPPBOX_ID + "=?";

    static {
        sOccupationQueryBuilder = new SQLiteQueryBuilder();

        sOccupationQueryBuilder.setTables(OccupationEntry.TABLE_NAME + " INNER JOIN " + ProjectEntry.TABLE_NAME +
        " ON " + OccupationEntry.TABLE_NAME + "." + OccupationEntry.COLUMN_LOCAL_PROJECT_ID +
        " = " + ProjectEntry.TABLE_NAME + "." + ProjectEntry._ID + " INNER JOIN " + UserEntry.TABLE_NAME +
        " ON " + OccupationEntry.TABLE_NAME + "." + OccupationEntry.COLUMN_LOCAL_USER_ID +
        " = " + UserEntry.TABLE_NAME + "." + UserEntry._ID);
    }

    public static Cursor query_Occupation(@NonNull Uri uri, String[] projection, String selection, String[] args, String sortOrder, GrappboxDBHelper openHelper){
        return sOccupationQueryBuilder.query(openHelper.getReadableDatabase(), projection, selection, args, null, null, sortOrder);
    }
    public static Cursor query_OccupationAllByProjectId(@NonNull Uri uri, String[] projection, String selection, String[] args, String sortOrder, GrappboxDBHelper openHelper){
        return sOccupationQueryBuilder.query(openHelper.getReadableDatabase(), projection, sProjectIdSelection, new String[]{uri.getLastPathSegment()}, null, null, sortOrder);
    }

    public static Cursor query_OccupationAllByGrappboxProjectId(@NonNull Uri uri, String[] projection, String selection, String[] args, String sortOrder, GrappboxDBHelper openHelper){
        return sOccupationQueryBuilder.query(openHelper.getReadableDatabase(), projection, sGrappboxProjectIdSelection, new String[]{uri.getLastPathSegment()}, null, null, sortOrder);
    }

    public static Uri insert(@Nullable Uri uri, ContentValues contentValues, GrappboxDBHelper openHelper)
    {
        long id = openHelper.getWritableDatabase().insert(OccupationEntry.TABLE_NAME, null, contentValues);
        if (id <= 0)
            throw new android.database.SQLException("Failed to insert row into " + uri);
        return OccupationEntry.buildOccupationWithLocalIdUri(id);
    }

    public static int update(@NonNull Uri uri, ContentValues contentValues, String selection, String[] args, GrappboxDBHelper openHelper) {
        return openHelper.getWritableDatabase().update(OccupationEntry.TABLE_NAME, contentValues, selection, args);
    }

    public static int bulkInsert(Uri uri, ContentValues[] values, GrappboxDBHelper openHelper) {
        final SQLiteDatabase db = openHelper.getWritableDatabase();
        int returnCount = 0;

        db.beginTransaction();
        try {
            for (ContentValues value : values) {
                long _id = db.insert(OccupationEntry.TABLE_NAME, null, value);
                if (_id > 0)
                    ++returnCount;
                db.setTransactionSuccessful();
            }
        } finally {
            db.endTransaction();
        }
        return returnCount;
    }
}
