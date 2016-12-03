using Grappbox.Helpers;
using Grappbox.HttpRequest;
using Grappbox.Model;
using System;
using System.Collections.Generic;
using System.Collections.ObjectModel;
using System.Diagnostics;
using System.Linq;
using System.Text;
using System.Threading.Tasks;
using Windows.UI.Popups;
using Windows.Web.Http;

namespace Grappbox.ViewModel
{
    public class GenericDashboardViewModel : ViewModelBase
    {
        private ObservableCollection<ProjectListModel> _projectList;

        public ObservableCollection<ProjectListModel> ProjectList
        {
            get { return _projectList; }
            set { _projectList = value; NotifyPropertyChanged("ProjectList"); }
        }

        public async Task<bool> getProjectList()
        {
            HttpResponseMessage res = await HttpRequestManager.Get(null, Constants.DashboardGenericCall);
            if (res == null)
                return false;
            string response = await res.Content.ReadAsStringAsync();
            Debug.WriteLine("Url= " + res.RequestMessage.RequestUri.ToString());
            Debug.WriteLine("response= " + response);
            if (res.IsSuccessStatusCode)
            {
                ProjectList = SerializationHelper.DeserializeArrayJson<ObservableCollection<ProjectListModel>>(response);
            }
            else
            {
                MessageDialog msgbox = new MessageDialog(HttpRequestManager.GetErrorMessage(response));
                await msgbox.ShowAsync();
                return false;
            }
            return true;
        }
    }
}