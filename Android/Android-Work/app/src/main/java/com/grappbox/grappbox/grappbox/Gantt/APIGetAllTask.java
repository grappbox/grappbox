package com.grappbox.grappbox.grappbox.Gantt;

import android.content.Context;
import android.os.AsyncTask;

import com.grappbox.grappbox.grappbox.Model.APIConnectAdapter;
import com.grappbox.grappbox.grappbox.Model.SessionAdapter;

import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

import java.io.IOException;
import java.text.ParseException;
import java.util.ArrayList;
import java.util.Collections;

/**
 * Created by wieser_m on 22/04/2016.
 */
public class APIGetAllTask extends AsyncTask<String, Void, String> {
    APIConnectAdapter api;
    Context context;
    APIGetAllTaskListener listener;

    public interface APIGetAllTaskListener
    {
        void onTaskFetched(ArrayList<Task> tasks);
    }

    public APIGetAllTask(Context context, APIGetAllTaskListener eventHandler) {
        this.api = APIConnectAdapter.getInstance(true);
        this.context = context;
        listener = eventHandler;
    }

    @Override
    protected void onPostExecute(String s) {
        super.onPostExecute(s);
        ArrayList<Task> tasksFetched = new ArrayList<>();
        if (s == null || s.isEmpty())
            return;
        try {
            JSONObject json = new JSONObject(s);
            JSONObject info = json.getJSONObject("info");
            if (TaskInfoHandler.process(context, api.getResponseCode(), info))
                return;
            JSONObject data = json.getJSONObject("data");
            JSONArray tasks = data.getJSONArray("array");
            for (int i = 0; i < tasks.length(); ++i)
                tasksFetched.add(new Task(tasks.getJSONObject(i)));
        } catch (JSONException | IOException | ParseException e) {
            e.printStackTrace();
        }
        if (listener != null)
            Collections.reverse(tasksFetched);
            listener.onTaskFetched(tasksFetched);
    }

    @Override
    protected String doInBackground(String... params) {
        if (params.length < 1)
            return null;
        String projectId = params[0];

        try {
            api.setVersion("V0.2");
            api.startConnection("tasks/getprojecttasks/" + SessionAdapter.getInstance().getToken() + "/" + projectId);
            api.setRequestConnection("GET");
            return api.getInputSream();
        } catch (IOException e) {
            e.printStackTrace();
        }
        return null;
    }
}
