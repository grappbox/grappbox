package com.grappbox.grappbox.grappbox.Dashboard;

import android.content.ContentValues;
import android.os.AsyncTask;
import android.support.v4.widget.SwipeRefreshLayout;
import android.util.Log;

import com.grappbox.grappbox.grappbox.Model.APIConnectAdapter;
import com.grappbox.grappbox.grappbox.Model.SessionAdapter;

import org.json.JSONException;

import java.io.IOException;
import java.util.List;

public class APIRequestGlobalProgress extends AsyncTask<String, Void, List<ContentValues>> {

    private final static String _PATH = "dashboard/getprojectsglobalprogress/";
    private SwipeRefreshLayout _swiper = null;
    private GlobalProgressFragment _context;

    APIRequestGlobalProgress(GlobalProgressFragment context)
    {
        _context = context;
    }

    @Override
    protected void onPostExecute(List<ContentValues> result) {
        super.onPostExecute(result);
        if (result != null) {
            _context.createContentView(result);
        }
        if (_swiper != null)
            _swiper.setRefreshing(false);
    }

    public void SetRefreshSwiper(SwipeRefreshLayout swiper)
    {
        _swiper = swiper;
    }

    @Override
    protected List<ContentValues> doInBackground(String ... param)
    {
        List<ContentValues> contentAPI = null;
        Integer APIResponse;
        String resultAPI;

        try {
            APIConnectAdapter.getInstance().startConnection(_PATH + SessionAdapter.getInstance().getUserData(SessionAdapter.KEY_TOKEN));
            APIConnectAdapter.getInstance().setRequestConnection("GET");

            resultAPI = APIConnectAdapter.getInstance().getInputSream();
            APIResponse = APIConnectAdapter.getInstance().getResponseCode();
            Log.v("JSON Response", String.valueOf(APIResponse));
            if (APIResponse == 200) {
                Log.v("JSON Content", resultAPI);
                contentAPI = APIConnectAdapter.getInstance().getListGlobalProgress(resultAPI);
            }

        } catch (IOException |JSONException e){
            e.printStackTrace();
            return null;
        } finally {
            APIConnectAdapter.getInstance().closeConnection();
        }
        return contentAPI;
    }

}
