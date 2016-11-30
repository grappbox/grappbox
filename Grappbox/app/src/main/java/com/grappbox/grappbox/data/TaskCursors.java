/*
 * Created by Marc Wieser the 18/11/2016
 * If you have any problem or question about this work
 * please contact the author at marc.wieser@gmail.com
 *
 * The following code is owned by GrappBox you can't
 * use it without any authorization or special instructions
 * GrappBox © 2016
 */

package com.grappbox.grappbox.data;


import android.content.ContentValues;
import android.database.Cursor;
import android.database.SQLException;
import android.database.sqlite.SQLiteQueryBuilder;
import android.net.Uri;
import android.support.annotation.NonNull;

import com.grappbox.grappbox.data.GrappboxContract.TaskAssignationEntry;
import com.grappbox.grappbox.data.GrappboxContract.TaskEntry;
import com.grappbox.grappbox.data.GrappboxContract.TaskTagAssignationEntry;
import com.grappbox.grappbox.data.GrappboxContract.TaskTagEntry;
import com.grappbox.grappbox.data.GrappboxContract.UserEntry;

public class TaskCursors {
    private static SQLiteQueryBuilder sTagBuilder, sUserBuilder;

    static {
        sTagBuilder = new SQLiteQueryBuilder();
        sTagBuilder.setTables(TaskEntry.TABLE_NAME +
        " INNER JOIN " + TaskTagAssignationEntry.TABLE_NAME +
        " ON " + TaskEntry.TABLE_NAME + "." + TaskEntry._ID + " = " + TaskTagAssignationEntry.TABLE_NAME + "." + TaskTagAssignationEntry.COLUMN_LOCAL_TASK +
        " INNER JOIN " + TaskTagEntry.TABLE_NAME +
        " ON " + TaskTagAssignationEntry.TABLE_NAME + "." + TaskTagAssignationEntry.COLUMN_LOCAL_TAG + " = " + TaskTagEntry.TABLE_NAME + "." + TaskTagEntry._ID) ;

        sUserBuilder = new SQLiteQueryBuilder();
        sUserBuilder.setTables(TaskEntry.TABLE_NAME +
        " INNER JOIN " + TaskAssignationEntry.TABLE_NAME +
        " ON " + TaskEntry.TABLE_NAME + "." + TaskEntry._ID + " = " + TaskAssignationEntry.TABLE_NAME + "." + TaskAssignationEntry.COLUMN_LOCAL_TASK +
        " INNER JOIN " + UserEntry.TABLE_NAME +
        " ON " + TaskAssignationEntry.TABLE_NAME + "." + TaskAssignationEntry.COLUMN_LOCAL_USER_ID + " = " + UserEntry.TABLE_NAME + "." + UserEntry._ID) ;
    }

    public static Cursor query(@NonNull Uri uri, String[] projection, String selection, String[] args, String sortOrder, GrappboxDBHelper openHelper){
        return openHelper.getReadableDatabase().query(TaskEntry.TABLE_NAME, projection, selection, args, null, null, sortOrder);
    }

    public static Cursor query_withTag(@NonNull Uri uri, String[] projection, String selection, String[] args, String sortOrder, GrappboxDBHelper openHelper){
        return sTagBuilder.query(openHelper.getReadableDatabase(), projection, selection, args, null, null, sortOrder);
    }

    public static Cursor query_withUser(@NonNull Uri uri, String[] projection, String selection, String[] args, String sortOrder, GrappboxDBHelper openHelper){
        return sUserBuilder.query(openHelper.getReadableDatabase(), projection, selection, args, null, null, sortOrder);
    }

    public static Uri insert(@NonNull Uri uri, ContentValues values, GrappboxDBHelper openHelper){
        long id = openHelper.getWritableDatabase().insert(TaskEntry.TABLE_NAME, null, values);
        if (id < 0)
            throw new SQLException("Failed to insert row into " + uri);
        return TaskEntry.buildProjectWithId(id);
    }

    public static int update(Uri uri, ContentValues contentValues, String selection, String[] args, GrappboxDBHelper mOpenHelper){
        return mOpenHelper.getWritableDatabase().update(TaskEntry.TABLE_NAME, contentValues, selection, args);
    }
}
