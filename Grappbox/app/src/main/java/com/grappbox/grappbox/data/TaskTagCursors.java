/*
 * Created by Marc Wieser on 18/11/2016
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
import android.net.Uri;
import android.support.annotation.NonNull;

public class TaskTagCursors {

    public static Cursor query(@NonNull Uri uri, String[] projection, String selection, String[] args, String sortOrder, GrappboxDBHelper openHelper){
        return openHelper.getReadableDatabase().query(GrappboxContract.TaskTagEntry.TABLE_NAME, projection, selection, args, null, null, sortOrder);
    }

    public static Uri insert(@NonNull Uri uri, ContentValues values, GrappboxDBHelper openHelper){
        long id = openHelper.getWritableDatabase().insert(GrappboxContract.TaskTagEntry.TABLE_NAME, null, values);
        if (id < 0)
            throw new SQLException("Failed to insert row into " + uri);
        return GrappboxContract.TaskTagEntry.buildProjectWithId(id);
    }

    public static int update(Uri uri, ContentValues contentValues, String selection, String[] args, GrappboxDBHelper mOpenHelper){
        return mOpenHelper.getWritableDatabase().update(GrappboxContract.TaskTagEntry.TABLE_NAME, contentValues, selection, args);
    }
}
