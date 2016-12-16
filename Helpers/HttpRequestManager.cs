﻿using Grappbox.Helpers;
using Grappbox.Model;
using Grappbox.ViewModel;
using Newtonsoft.Json;
using Newtonsoft.Json.Linq;
using System;
using System.Collections.Generic;
using System.Diagnostics;
using System.Text;
using System.Threading.Tasks;
using Windows.Web.Http;
using Windows.Web.Http.Headers;

namespace Grappbox.HttpRequest
{
    //This class is a singleton
    public static class HttpRequestManager
    {
        #region Private members

        private const string baseAdress = "https://api.grappbox.com/";
        private const string version = "0.3/";

        private const string baseUrl = baseAdress + version;

        #endregion Private members

        #region Utils

        private static void InitHeaders(HttpClient client)
        {
            client.DefaultRequestHeaders.Accept.Clear();
            client.DefaultRequestHeaders.Accept.Add(new Windows.Web.Http.Headers.HttpMediaTypeWithQualityHeaderValue("application/json"));
            SessionHelper session = SessionHelper.GetSession();
            if (session != null)
                client.DefaultRequestHeaders.Authorization = new HttpCredentialsHeaderValue(session.UserToken);
        }

        private static Uri RequestUri(string requestUrl)
        {
            Uri reqUri = new Uri(baseUrl + requestUrl);
            return reqUri;
        }

        public static string GetErrorMessage(string jsonTxt)
        {
            if (jsonTxt == "")
                return ("No internet connection");
            string message = "Undeterminate Error";
            try
            {
                JObject info = (JObject)JObject.Parse(jsonTxt).GetValue("info");
                message = info.GetValue("return_message").ToString();
                string[] split = message.Split('-');
                message = split[2];
            }
            catch (Exception ex)
            {
                return ex.Message;
            }
            return message;
        }

        #endregion Utils

        #region Requests

        public static async Task<HttpResponseMessage> Post(Dictionary<string, object> properties, string url)
        {
            JObject post = new JObject();
            JObject data = new JObject();
            foreach (KeyValuePair<string, object> it in properties)
            {
                data.Add(it.Key, JToken.FromObject(it.Value));
            }
            post.Add("data", JToken.FromObject(data));
            using (var httpClient = new HttpClient())
            {
                InitHeaders(httpClient);
                HttpStringContent sc = null;
                HttpResponseMessage res = null;
                try
                {
                    sc = new HttpStringContent(post.ToString());
                    res = await httpClient.PostAsync(new Uri(baseUrl + url), sc);
                }
                catch (Exception ex)
                {
                    Debug.WriteLine(ex.Message);
                    return null;
                }
                finally
                {

                    sc?.Dispose();
                }
                return res;
            }
        }

        public static async Task<bool> Login(string username, string password)
        {
            JObject post = new JObject();
            JObject data = new JObject();

            data.Add("login", JToken.FromObject(username));
            data.Add("password", JToken.FromObject(password));
            data.Add("mac", SystemInformation.GetUniqueIdentifier());
            data.Add("flag", JToken.FromObject("wph"));
            data.Add("is_client", JToken.FromObject(false));
            data.Add("device_name", JToken.FromObject("WindowsPhone"));
            post.Add("data", JToken.FromObject(data));

            using (var httpClient = new HttpClient())
            {
                HttpStringContent sc = null;
                HttpResponseMessage res = null;
                try
                {
                    sc = new HttpStringContent(post.ToString());
                    res = await httpClient.PostAsync(new Uri(baseUrl + "account/login"), sc);
                }
                catch (Exception ex)
                {
                    Debug.WriteLine(ex.Message);
                    res?.Dispose();
                    return false;
                }
                finally
                {
                    sc?.Dispose();
                }
                if (res.IsSuccessStatusCode)
                {
                    try
                    {
                        var user = SerializationHelper.DeserializeJson<UserModel>(await res.Content.ReadAsStringAsync());
                        SessionHelper.CreateSessionHelper(user);
                    }
                    catch (Exception ex)
                    {
                        Debug.WriteLine(ex.Message);
                        return false;
                    }
                    finally
                    {
                        res?.Dispose();
                    }
                    var session = SessionHelper.GetSession();
                    if (session.IsUserConnected == true)
                    {
                        string token = session.UserToken;
                        return true;
                    }
                    else
                        return false;
                }
                else
                    return false;
            }
        }

        public static async Task<HttpResponseMessage> Put(Dictionary<string, object> properties, string url)
        {
            JObject put = new JObject();
            JObject data = new JObject();
            foreach (KeyValuePair<string, object> it in properties)
            {
                data.Add(it.Key, JToken.FromObject(it.Value));
            }
            put.Add("data", JToken.FromObject(data));
            using (var httpClient = new HttpClient())
            {
                InitHeaders(httpClient);
                HttpStringContent sc = null;
                HttpResponseMessage res = null;
                try
                {
                    sc = new HttpStringContent(put.ToString(), Windows.Storage.Streams.UnicodeEncoding.Utf8, "application/json");
                    res = await httpClient.PutAsync(RequestUri(url), sc);
                }
                catch (Exception ex)
                {
                    Debug.WriteLine(ex.Message);
                    res?.Dispose();
                    sc?.Dispose();
                    return null;
                }
                finally
                {
                    sc?.Dispose();
                }
                return res;
            }
        }

        public static async Task<HttpResponseMessage> Get(object[] values, string url)
        {
            using (var httpClient = new HttpClient())
            {
                InitHeaders(httpClient);
                HttpResponseMessage res = null;
                try
                {
                    StringBuilder getParam = new StringBuilder("");
                    if (values != null)
                    {
                        getParam.Append("/");
                        for (int i = 0; i < values.Length; ++i)
                        {
                            getParam.Append(values[i]);
                            if (i + 1 < values.Length)
                                getParam.Append("/");
                        }
                    }
                    res = await httpClient.GetAsync(RequestUri(url + getParam));
                }
                catch (Exception ex)
                {
                    Debug.WriteLine(ex.Message);
                    res?.Dispose();
                    return null;
                }
                return res;
            }
        }

        public static async Task<HttpResponseMessage> Delete(object[] values, string url)
        {
            using (var httpClient = new HttpClient())
            {
                InitHeaders(httpClient);
                HttpResponseMessage res = null;
                try
                {
                    StringBuilder del = new StringBuilder("/");
                    for (int i = 0; i < values.Length; ++i)
                    {
                        del.Append(values[i]);
                        if (i + 1 < values.Length)
                            del.Append("/");
                    }
                    res = await httpClient.DeleteAsync(RequestUri(url + del));
                }
                catch (Exception ex)
                {
                    Debug.WriteLine(ex.Message);
                    return null;
                }
                return res;
            }
        }

        #endregion Requests
    }
}