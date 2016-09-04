package com.grappbox.grappbox.data;

import android.database.Cursor;
import android.database.sqlite.SQLiteQueryBuilder;
import android.net.Uri;
import android.support.annotation.NonNull;

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
}
