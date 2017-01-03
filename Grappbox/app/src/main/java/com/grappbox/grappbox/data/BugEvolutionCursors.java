package com.grappbox.grappbox.data;

import android.content.ContentValues;
import android.database.Cursor;
import android.database.SQLException;
import android.database.sqlite.SQLiteDatabase;
import android.database.sqlite.SQLiteQueryBuilder;
import android.net.Uri;
import android.support.annotation.NonNull;

import com.grappbox.grappbox.data.GrappboxContract.BugEvolutionEntry;
import com.grappbox.grappbox.data.GrappboxContract.StatEntry;
/**
 * Created by tan_f on 16/12/2016.
 */

public class BugEvolutionCursors {

    private static final SQLiteQueryBuilder sBugEvolutionWithStat;

    static {
        sBugEvolutionWithStat = new SQLiteQueryBuilder();
        sBugEvolutionWithStat.setTables(BugEvolutionEntry.TABLE_NAME + " INNER JOIN " + StatEntry.TABLE_NAME +
        " ON " + BugEvolutionEntry.TABLE_NAME + "." + BugEvolutionEntry.COLUMN_LOCAL_STAT_ID + " = " + StatEntry.TABLE_NAME + "." + StatEntry._ID);
    }

    public static Cursor query_BugEvolution(@NonNull Uri uri, String[] projection, String selection, String[] args, String sortOrder, GrappboxDBHelper openHelper) {
        return openHelper.getReadableDatabase().query(BugEvolutionEntry.TABLE_NAME, projection, selection, args, null, null, sortOrder);
    }

    public static Cursor query_BugEvolutionById(@NonNull Uri uri, String[] projection, String selection, String[] args, String sortOrder, GrappboxDBHelper openHelper) {
        return openHelper.getReadableDatabase().query(BugEvolutionEntry.TABLE_NAME, projection, BugEvolutionEntry._ID + "=?", new String[]{uri.getLastPathSegment()}, null, null, sortOrder);
    }

    public static Cursor query_BugEvolutionByStat(@NonNull Uri uri, String[] projection, String selection, String[] args, String sortOrder, GrappboxDBHelper openHelper) {
        return sBugEvolutionWithStat.query(openHelper.getReadableDatabase(), projection, selection, args, null, null, sortOrder);
    }

    public static Uri insert(@NonNull Uri uri, ContentValues values, GrappboxDBHelper openHelper) {
        long id = openHelper.getWritableDatabase().insert(BugEvolutionEntry.TABLE_NAME, null, values);
        if (id <= 0)
            throw new SQLException("Failed to insert row into " + uri);
        return BugEvolutionEntry.buildBugEvolutionLocalUri(id);
    }

    public static int bulkinsert(@NonNull Uri uri, ContentValues[] values, GrappboxDBHelper openHelper) {
        final SQLiteDatabase db = openHelper.getWritableDatabase();
        int returnCount = 0;

        db.beginTransaction();
        try {
            for (ContentValues value : values) {
                long _id = db.insert(BugEvolutionEntry.TABLE_NAME, null, value);
                if (_id > 0)
                    ++returnCount;
                db.setTransactionSuccessful();
            }
        } finally {
            db.endTransaction();
        }
        return returnCount;
    }

    public static int update(Uri uri, ContentValues values, String selection, String[] args, GrappboxDBHelper mOpenHelper) {
        return mOpenHelper.getWritableDatabase().update(BugEvolutionEntry.TABLE_NAME, values, selection, args);
    }
}
