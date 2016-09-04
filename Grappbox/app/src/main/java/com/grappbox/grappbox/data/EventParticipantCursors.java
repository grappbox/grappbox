package com.grappbox.grappbox.data;

import android.database.Cursor;
import android.database.sqlite.SQLiteQueryBuilder;
import android.net.Uri;
import android.support.annotation.NonNull;

import com.grappbox.grappbox.data.GrappboxContract.EventEntry;
import com.grappbox.grappbox.data.GrappboxContract.EventParticipantEntry;
import com.grappbox.grappbox.data.GrappboxContract.UserEntry;

/**
 * Created by marcw on 30/08/2016.
 */
public class EventParticipantCursors {
    private static final SQLiteQueryBuilder sEventParticipantQueryBuilder;

    private static final String sEventIdSelection = EventEntry.TABLE_NAME + "." + EventEntry._ID + "=?";
    private static final String sEventGrappboxIdSelection = EventEntry.TABLE_NAME + "." + EventEntry.COLUMN_GRAPPBOX_ID + "=?";

    static {
        sEventParticipantQueryBuilder = new SQLiteQueryBuilder();
        sEventParticipantQueryBuilder.setTables(EventParticipantEntry.TABLE_NAME + " INNER JOIN " +
        EventEntry.TABLE_NAME + " ON " + EventParticipantEntry.TABLE_NAME + "." + EventParticipantEntry.COLUMN_LOCAL_EVENT_ID +
        " = " + EventEntry.TABLE_NAME + "." + EventEntry._ID + " INNER JOIN " +
        UserEntry.TABLE_NAME + " ON " + EventParticipantEntry.TABLE_NAME + "." + EventParticipantEntry.COLUMN_LOCAL_USER_ID +
        " = " + UserEntry.TABLE_NAME + "." + UserEntry._ID);
    }

    public static Cursor query_EventParticipant(@NonNull Uri uri, String[] projection, String selection, String[] args, String sortOrder, GrappboxDBHelper openHelper){
        return sEventParticipantQueryBuilder.query(openHelper.getReadableDatabase(), projection, selection, args, null, null, sortOrder);
    }

    public static Cursor query_EventParticipantByEventId(@NonNull Uri uri, String[] projection, String selection, String[] args, String sortOrder, GrappboxDBHelper openHelper){
        return sEventParticipantQueryBuilder.query(openHelper.getReadableDatabase(), projection, sEventIdSelection, new String[]{uri.getLastPathSegment()}, null, null, sortOrder);
    }

    public static Cursor query_EventParticipantByGrappboxEventId(@NonNull Uri uri, String[] projection, String selection, String[] args, String sortOrder, GrappboxDBHelper openHelper){
        return sEventParticipantQueryBuilder.query(openHelper.getReadableDatabase(), projection, sEventGrappboxIdSelection, new String[]{uri.getLastPathSegment()}, null, null, sortOrder);
    }
}
