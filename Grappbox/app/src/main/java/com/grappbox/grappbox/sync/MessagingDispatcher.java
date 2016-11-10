/*
 * Created by Marc Wieser on 10/11/2016
 * If you have any problem or question about this work
 * please contact the author at marc.wieser@gmail.com
 *
 * The following code is owned by GrappBox you can't
 * use it without any authorization or special instructions
 * GrappBox © 2016
 */

package com.grappbox.grappbox.sync;


import org.json.JSONObject;

public interface MessagingDispatcher {
    void dispatch(String action, JSONObject body);
}
