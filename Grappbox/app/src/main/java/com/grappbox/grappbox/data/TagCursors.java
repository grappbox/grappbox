package com.grappbox.grappbox.data;

import android.database.Cursor;
import android.database.sqlite.SQLiteQueryBuilder;
import android.net.Uri;
import android.support.annotation.NonNull;

import com.grappbox.grappbox.data.GrappboxContract.ProjectEntry;
import com.grappbox.grappbox.data.GrappboxContract.TagEntry;

/**
 * Created by marcw on 30/08/2016.
 */
public class TagCursors {
    private static final SQLiteQueryBuilder sQueryBuilder;

    private static final String sTagIdSelection = TagEntry.TABLE_NAME + "." + TagEntry._ID + "=?";
    private static final String sTagGrappboxIdSelection = TagEntry.TABLE_NAME + "." + TagEntry.COLUMN_GRAPPBOX_ID + "=?";
    private static final String sProjectIdSelection = ProjectEntry.TABLE_NAME + "." + ProjectEntry._ID + "=?";
    private static final String sProjectGrappboxIdSelection = ProjectEntry.TABLE_NAME + "." + ProjectEntry.COLUMN_GRAPPBOX_ID + "=?";

    static {
        sQueryBuilder = new SQLiteQueryBuilder();
        sQueryBuilder.setTables(TagEntry.TABLE_NAME + " INNER JOIN " + ProjectEntry.TABLE_NAME +
        " ON " + TagEntry.TABLE_NAME + "." + TagEntry.COLUMN_LOCAL_PROJECT_ID +
        " = " + ProjectEntry.TABLE_NAME + "." + ProjectEntry._ID);
    }

    public static Cursor query_Tag(@NonNull Uri uri, String[] projection, String selection, String[] args, String sortOrder, GrappboxDBHelper openHelper){
        return sQueryBuilder.query(openHelper.getReadableDatabase(), projection, selection, args, null, null, sortOrder);
    }

    public static Cursor query_TagById(@NonNull Uri uri, String[] projection, String selection, String[] args, String sortOrder, GrappboxDBHelper openHelper){
        return sQueryBuilder.query(openHelper.getReadableDatabase(), projection, sTagIdSelection, new String[]{uri.getLastPathSegment()}, null, null, sortOrder);
    }

    public static Cursor query_TagByGrappboxId(@NonNull Uri uri, String[] projection, String selection, String[] args, String sortOrder, GrappboxDBHelper openHelper){
        return sQueryBuilder.query(openHelper.getReadableDatabase(), projection, sTagGrappboxIdSelection, new String[]{uri.getLastPathSegment()}, null, null, sortOrder);
    }

    public static Cursor query_TagByProjectId(@NonNull Uri uri, String[] projection, String selection, String[] args, String sortOrder, GrappboxDBHelper openHelper){
        return sQueryBuilder.query(openHelper.getReadableDatabase(), projection, sProjectIdSelection, new String[]{uri.getLastPathSegment()}, null, null, sortOrder);
    }

    public static Cursor query_TagByGrappboxProjectId(@NonNull Uri uri, String[] projection, String selection, String[] args, String sortOrder, GrappboxDBHelper openHelper){
        return sQueryBuilder.query(openHelper.getReadableDatabase(), projection, sProjectGrappboxIdSelection, new String[]{uri.getLastPathSegment()}, null, null, sortOrder);
    }
}
