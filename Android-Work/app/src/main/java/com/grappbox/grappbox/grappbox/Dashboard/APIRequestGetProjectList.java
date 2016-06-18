package com.grappbox.grappbox.grappbox.Dashboard;

import android.content.ContentValues;
import android.os.AsyncTask;
import android.support.v4.widget.SwipeRefreshLayout;
import android.util.Log;

import com.grappbox.grappbox.grappbox.Model.APIConnectAdapter;
import com.grappbox.grappbox.grappbox.Model.ProjectModel;
import com.grappbox.grappbox.grappbox.Model.SessionAdapter;

import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

import java.io.IOException;
import java.util.List;

/**
 * Created by tan_f on 15/06/2016.
 */
public class APIRequestGetProjectList extends AsyncTask<String, Void, String> {

    private final static String _PATH = "user/getprojects/";
    private DashboardProjectListFragment _context;
    private SwipeRefreshLayout _swiper;
    private DashboardRVAdapter _adapter;
    private  boolean _toBeClear;

    APIRequestGetProjectList(DashboardProjectListFragment context, DashboardRVAdapter adapter, boolean toBeClear)
    {
        _context = context;
        _adapter = adapter;
        _toBeClear = toBeClear;
    }

    public void SetRefreshSwiper(SwipeRefreshLayout swiper)
    {
        _swiper = swiper;
    }

    @Override
    protected void onPostExecute(String result) {
        super.onPostExecute(result);

        JSONObject data = null;

        if (result != null) {
            try {
                data = new JSONObject(result).getJSONObject("data");
                JSONArray array = data.getJSONArray("array");
                if (array != null && _adapter != null){
                    if (_toBeClear){
                        _adapter.clearData();
                    }
                    for (int i = 0; i < array.length(); ++i){
                        JSONObject obj = array.getJSONObject(i);
                        _adapter.insertData(new ProjectModel(obj), -1);
                    }
                }
                if (_swiper != null)
                    _swiper.setRefreshing(false);
                _context.fillView();
            } catch (JSONException e){
                e.printStackTrace();
            }
        }
    }

    @Override
    protected String doInBackground(String ... param)
    {
        Integer APIResponse;
        String resultAPI = null;

        try {
            APIConnectAdapter.getInstance().startConnection(_PATH + SessionAdapter.getInstance().getUserData(SessionAdapter.KEY_TOKEN));
            APIConnectAdapter.getInstance().setRequestConnection("GET");


            APIResponse = APIConnectAdapter.getInstance().getResponseCode();
            Log.v("JSON Response", String.valueOf(APIResponse));
            if (APIResponse == 200) {
                resultAPI = APIConnectAdapter.getInstance().getInputSream();
                Log.v("JSON Content", resultAPI);
            }

        } catch (IOException e){
            e.printStackTrace();
            return null;
        } finally {
            APIConnectAdapter.getInstance().closeConnection();
        }
        return resultAPI;
    }

}