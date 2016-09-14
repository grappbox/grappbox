package com.grappbox.grappbox;

import android.support.test.InstrumentationRegistry;
import android.support.test.rule.ActivityTestRule;
import android.support.test.runner.AndroidJUnit4;
import android.test.suitebuilder.annotation.LargeTest;

import com.grappbox.grappbox.data.GrappboxDBHelper;

import org.junit.Rule;
import org.junit.Test;
import org.junit.runner.RunWith;

import static junit.framework.Assert.assertEquals;
import static junit.framework.Assert.assertTrue;

/**
 * <a href="http://d.android.com/tools/testing/testing_android.html">Testing Fundamentals</a>
 */
@RunWith(AndroidJUnit4.class)
@LargeTest
public class ApplicationTest {
    @Rule
    public ActivityTestRule<ChooseProjectActivity> mMainActivityTestRule = new ActivityTestRule<>(ChooseProjectActivity.class);

    @Test
    public void testFloattingButton() throws Throwable
    {
        boolean success = InstrumentationRegistry.getInstrumentation().getTargetContext().deleteDatabase(GrappboxDBHelper.DATABASE_NAME);
        assertEquals(success, true);
    }
}
