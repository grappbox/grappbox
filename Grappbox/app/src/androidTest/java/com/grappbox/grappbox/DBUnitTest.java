package com.grappbox.grappbox;


import android.content.ContentValues;
import android.net.Uri;
import android.support.test.InstrumentationRegistry;
import android.support.test.runner.AndroidJUnit4;
import android.test.AndroidTestCase;
import android.util.Log;

import com.grappbox.grappbox.data.GrappboxContract;
import com.grappbox.grappbox.data.GrappboxContract.ProjectEntry;

import org.junit.Test;
import org.junit.runner.RunWith;

/**
 * Created by marc on 23/09/2016.
 */
@RunWith(AndroidJUnit4.class)
public class DBUnitTest extends AndroidTestCase {


    @Override
    public void setUp() throws Exception {
        super.setUp();
    }

    @Test
    public void insertTest() throws Exception{
        final String JUnitGrappboxID = "42";

        Uri[] testedUris = new Uri[]{
                GrappboxContract.UserEntry.CONTENT_URI,
                ProjectEntry.CONTENT_URI,
                GrappboxContract.CloudEntry.CONTENT_URI
        };
        long[] insertedIds = new long[testedUris.length];
        ContentValues[] values = new ContentValues[testedUris.length];

        ContentValues projectValues = new ContentValues();
        ContentValues userValues = new ContentValues();
        ContentValues cloudValues = new ContentValues();

        projectValues.put(ProjectEntry.COLUMN_COLOR, "#dadbdc");
        projectValues.put(ProjectEntry.COLUMN_COMPANY_NAME, "Test Inc.");
        projectValues.put(ProjectEntry.COLUMN_CONTACT_EMAIL, "provider@test.grappbox.com");
        projectValues.put(ProjectEntry.COLUMN_CONTACT_PHONE, "0102030405");
        projectValues.put(ProjectEntry.COLUMN_COUNT_BUG, 0);
        projectValues.put(ProjectEntry.COLUMN_COUNT_TASK, 0);
        projectValues.put(ProjectEntry.COLUMN_NAME, "Test JUnit");
        projectValues.putNull(ProjectEntry.COLUMN_DATE_DELETED_UTC);
        projectValues.put(ProjectEntry.COLUMN_DATE_LOGO_LAST_EDITED_UTC, "1945-06-18 06:00:00");
        projectValues.put(ProjectEntry.COLUMN_DESCRIPTION, "JUnit Test project");
        projectValues.put(ProjectEntry.COLUMN_GRAPPBOX_ID, JUnitGrappboxID);
        projectValues.put(ProjectEntry.COLUMN_SOCIAL_FACEBOOK, "http://facebook.com/junit");
        projectValues.put(ProjectEntry.COLUMN_SOCIAL_TWITTER, "http://twitter.com/junit");
        projectValues.putNull(ProjectEntry.COLUMN_URI_LOGO);
        values[1] = projectValues;

        userValues.put(GrappboxContract.UserEntry.COLUMN_FIRSTNAME, "Jay");
        userValues.put(GrappboxContract.UserEntry.COLUMN_LASTNAME, "Son");
        userValues.put(GrappboxContract.UserEntry.COLUMN_CONTACT_EMAIL, "jay.son@test.grappbox.com");
        userValues.put(GrappboxContract.UserEntry.COLUMN_CONTACT_PHONE, "0102030405");
        userValues.put(GrappboxContract.UserEntry.COLUMN_COUNTRY, "Testland");
        userValues.put(GrappboxContract.UserEntry.COLUMN_DATE_AVATAR_LAST_EDITED_UTC, "1945-06-18 06:00:00");
        userValues.put(GrappboxContract.UserEntry.COLUMN_SOCIAL_LINKEDIN, "http://linkedin.com/json");
        userValues.put(GrappboxContract.UserEntry.COLUMN_DATE_BIRTHDAY_UTC, "1945-06-18");
        userValues.put(GrappboxContract.UserEntry.COLUMN_SOCIAL_TWITTER, "http://twitter.com/json");
        userValues.put(GrappboxContract.UserEntry.COLUMN_GRAPPBOX_ID, JUnitGrappboxID);
        values[0] = userValues;

        cloudValues.put(GrappboxContract.CloudEntry.COLUMN_DATE_LAST_EDITED_UTC, "1945-06-18 06:00:00");
        cloudValues.put(GrappboxContract.CloudEntry.COLUMN_FILENAME, "JUnitTest.test");
        cloudValues.put(GrappboxContract.CloudEntry.COLUMN_IS_SECURED, 0);
        cloudValues.put(GrappboxContract.CloudEntry.COLUMN_MIMETYPE, "image/jpeg");
        cloudValues.put(GrappboxContract.CloudEntry.COLUMN_PATH, "/");
        cloudValues.put(GrappboxContract.CloudEntry.COLUMN_SIZE, 1024000);
        cloudValues.put(GrappboxContract.CloudEntry.COLUMN_TYPE, 0);
        values[2] = cloudValues;

        for (int i = 0; i < testedUris.length; ++i){
            Uri currentTest = testedUris[i];
            ContentValues currentValues = values[i];

            if (i == 1){
                Log.d("TEST", "Inject local creator id = " + insertedIds[0]);
                projectValues.put(ProjectEntry.COLUMN_LOCAL_CREATOR_ID, insertedIds[0]);
            } else if (i == 2){
                cloudValues.put(GrappboxContract.CloudEntry.COLUMN_LOCAL_PROJECT_ID, insertedIds[1]);
            }


            Uri insertedUri = InstrumentationRegistry.getContext().getContentResolver().insert(currentTest, currentValues);
            assertNotNull(insertedUri);
            assertNotNull(insertedUri.getLastPathSegment());
            long insertedId = Long.parseLong(insertedUri.getLastPathSegment());
            assertTrue(insertedId > -1);
            insertedIds[i] = insertedId;
        }
    }
}
