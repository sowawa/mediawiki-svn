

"""

This module is used to define the reporting methodologies on different types of data.  The base class
DataReporting is defined to outline the general functionality of the reporting architecture and 
functionality which includes generating the data via a dataloader object and transforming the data
among different reporting mediums including matlab plots (primary medium) and html tables.

The DataLoader class decouples the data access of the reports using the Adapter structural pattern.

"""

__author__ = "Ryan Faulkner"
__revision__ = "$Rev$"
__date__ = "December 16th, 2010"


import sys
# sys.path.append('../')

import matplotlib
import datetime
import MySQLdb
import pylab
import HTML
import math

import Fundraiser_Tools.classes.QueryData as QD
import Fundraiser_Tools.classes.Helper as Hlp
import Fundraiser_Tools.classes.TimestampProcessor as TP
import Fundraiser_Tools.classes.DataLoader as DL
import Fundraiser_Tools.classes.HypothesisTest as HT


matplotlib.use('Agg')

        
        
"""

    BASE CLASS :: DataReporting
    
    Base class for reporting fundraiser analytics.  Methods that are intended to be extended in derived classes include:
    
    METHODS:
    
        run_query                 - format and execute the query to obtain data
        gen_plot                 - plots the results of the report
        write_to_html_table     - writes the results to an HTML table
        run

"""
class DataReporting(object):    
    
    """ CLASS MEMBERS: Store the results of a query """
    _counts_ = None
    _times_ = None
    
    _font_size_ = 24
    _fig_width_pt_ = 246.0                  # Get this from LaTeX using \showthe\columnwidth
    _inches_per_pt_ = 1.0/72.27             # Convert pt to inch
    _use_labels_= False
    _fig_file_format_ = 'png'
    _plot_type_ = 'line'
    _item_keys_ = list()
    _file_path_ = './tests/'
    
    _data_loader_ = None
    _table_html_ = ''       # Stores the table html
    _data_plot_ = None      # Stores the plot object



    def __init__(self, **kwargs):
        
        for key in kwargs:
            
            if key == 'font_size':
                self._font_size_ = kwargs[key]
            elif key == 'fig_width_pt':
                self._fig_width_pt_ = kwargs[key]
            elif key == 'inches_per_pt':
                self._inches_per_pt_ = kwargs[key]
            elif key == 'use_labels':
                self._use_labels_ = kwargs[key]
            elif key == 'fig_file_format':
                self._fig_file_format_ = kwargs[key]
            elif key == 'plot_type':                
                self._plot_type_ = kwargs[key]
            elif key == 'item_keys':                
                self._item_keys_ = kwargs[key]
            elif key == 'file_path':                
                self._file_path_ = kwargs[key]
            
        
        # print self._data_loader_.__str__
        
    """

        Smooths a list of values
        
        INPUT:
                values               - a list of datetime objects
                window_length       - indicate whether the list counts back from the end
            
        RETURN: 
                new_values        - list of smoothed values

    """
    def smooth(self, values, window_length):

        window_length = int(math.floor(window_length / 2))

        if window_length < 1:
            return values

        list_len = len(values)
        new_values = list()

        for i in range(list_len):
            index_left = max([0, i - window_length])
            index_right = min([list_len - 1, i + window_length])

            width = index_right - index_left + 1
            
            new_val = sum(values[index_left : (index_right + 1)]) / width
            new_values.append(new_val)

        return new_values
    
    """
    
        workaround for issue with tuple objects in HTML.py 
        MySQLdb returns unfamiliar tuple elements from its fetchall() method
        this is probably a version problem since the issue popped up in 2.5 but not 2.6
        
        INPUT:
                row               - row object returned from MySQLdb.fetchall()
                
        RETURN: 
                l                - a list of tuple objects from the db
    
    """
    def listify(self, row):
        l = []
        for i in row:
            l.append(i)
        return l
        

    """
    
        To be overloaded by subclasses for specific types of queries
        
        INPUT:
                values               - a list of datetime objects
                window_length       - indicate whether the list counts back from the end
            
        RETURN: 
                return_status        - integer, 0 indicates un-exceptional execution
    
    """
    def run_query(self, start_time, end_time, query_name, metric_name):
        return 0
        
    
    """
    
        To be overloaded by subclasses for different plotting behaviour
        
        INPUT:
                values               - a list of datetime objects
                window_length       - indicate whether the list counts back from the end
            
        RETURN: 
                return_status        - integer, 0 indicates un-exceptional execution
    
    """
    def _gen_plot(self,x, y_lists, labels, title, xlabel, ylabel, subplot_index, fname):
        return 0

    """
    
        To be overloaded by subclasses for writing tables - this functionality currently exists outside of this class structure (test_reporting.py)
        
        INPUT:
                values               - a list of datetime objects
                window_length       - indicate whether the list counts back from the end
            
        RETURN: 
                return_status        - integer, 0 indicates un-exceptional execution
    
    """
    def _write_to_html_table(self):
        return 0
    
    
    
    """
    
        The access point of DataReporting and derived objects.  Will be used for executing and orchestrating the creation of plots, tables etc.
        To be overloaded by subclasses 
        
        INPUT:
             
        RETURN: 
                return_status        - integer, 0 indicates un-exceptional execution
                
    """
    def run(self):
        return
        


"""

    CLASS :: IntervalReporting
    
    Performs queries that take timestamps, query, and an interval as arguments.  Data for a single metric 
    is generated for each time interval in the time period defined by the start and end timestamps. 
    
    Types of queries supported:
    
    report_banner_metrics_minutely
    report_LP_metrics_minutely

"""

class IntervalReporting(DataReporting):
    
    
    
    """
        Constructor for IntervalReporting
        
        INPUT:
        
            loader_type    - string which determines the type of dataloader object
            **kwargs       - allows plotting parameters to be tuned     
    """
    def __init__(self, **kwargs):
        
        self._data_loader_ = DL.IntervalReportingLoader('')
        
        for key in kwargs:
            if key == 'query_type':                          # Set custom data loaders
                if kwargs[key] == 'campaign':
                    self._data_loader_ = DL.CampaignIntervalReportingLoader(kwargs[key])
                else:
                    self._data_loader_ = DL.IntervalReportingLoader(kwargs[key])
                
        """ Call constructor of parent """
        DataReporting.__init__(self, **kwargs)
        

        
         
    """
        <description>
    """    
    def usage(self): 
        
        """ !! MODIFY -- include instructions on using **kwargs """
        
        print 'Types of queries:'
        print '    (1) banner'
        print '    (2) LP'
        print ''
        print 'e.g.'
        print "    run('20101230160400', '20101230165400', 2, 'banner', 'imp', '20101230JA091_US', ['banner1', 'banner2'])"
        print "    run('20101230160400', '20101230165400', 2, 'LP', 'views', '20101230JA091_US', [])"
        print ''
        
        return

    """
        Selecting a subset of the key items in a dictionary       
        
        INPUT:
            dict_lists    - dictionary to be parsed
                        
        RETURN:
            new_dict_lists    - new dictionary containing only keys in self._item_keys_
    """
    def select_metric_keys(self, dict_lists):
        new_dict_lists = dict()
        
        dict_lists_keys = dict_lists.keys()
        
        for key in self._item_keys_:
            if key in dict_lists_keys:
                new_dict_lists[key] = dict_lists[key]
        
        return new_dict_lists
        
    """
        Protected method.  Execute reporting query and generate plots.       
        
        INPUT:
                        
        RETURN:
        
    """        
    def _gen_plot(self, metrics, times, title, xlabel, ylabel, ranges, subplot_index, fname, labels):
        
        pylab.subplot(subplot_index)
        pylab.figure(num=None,figsize=[26,14])    
        
        #line_types = ['b-o','g-o','r-o','c-o','m-o','k-o','y-o','b--d','g--d','r--d','c--d','m--d','k--d','y--d','b-.s','g-.s','r-.s','c-.s','m-.s','k-.s','y-.s']
        line_types = ['b-o','g-x','r-s','c-d','m-o','k-o','y-o','b--d','g--d','r--d','c--d','m--d','k--d','y--d','b-.s','g-.s','r-.s','c-.s','m-.s','k-.s','y-.s']
        
        count = 0
        for key in metrics.keys():
            if self._plot_type_ == 'step':
                pylab.step(times[key], metrics[key], line_types[count])
            elif self._plot_type_ == 'line':
                pylab.plot(times[key][1:], metrics[key][1:], line_types[count])
            count = count + 1
        
        """ Set the figure and font size """
        
        golden_mean = (math.sqrt(5)-1.0)/2.0                    # Aesthetic ratio
        fig_width = self._fig_width_pt_*self._inches_per_pt_    # width in inches
        fig_height = fig_width*golden_mean                      # height in inches
        fig_size =  [fig_width,fig_height]

        params = {'axes.labelsize': self._font_size_,
          'text.fontsize': self._font_size_,
          'xtick.labelsize': self._font_size_,
          'ytick.labelsize': self._font_size_,
          'legend.pad': 0.1,     # empty space around the legend box
          'legend.fontsize': self._font_size_,
          'font.size': self._font_size_,
          'text.usetex': False,
          'figure.figsize': fig_size}

        pylab.rcParams.update(params)

        pylab.grid()
        pylab.xlim(ranges[0], ranges[1])
        pylab.ylim(ranges[2], ranges[3])
        
        if self._use_labels_:
            pylab.legend(labels,loc=2)
        else:
            pylab.legend(metrics.keys(),loc=2)
        
        pylab.xlabel(xlabel)
        pylab.ylabel(ylabel)

        pylab.title(title)
        _data_plot_ = pylab.savefig(self._file_path_ + fname + '.' + self._fig_file_format_, format=self._fig_file_format_)

        
    """
    
        Protected method.  Takes the sum of interval metrics over time and writes them to an html table.
        
        RETURN:
            
            html    - html text for the resulting table
    """ 
    def _write_html_table(self):
        
        """ Combine the interval data """
        if self._data_loader_.combine_rows() == 0:
            print >> sys.stderr, 'No summary data for this reporting object.\n'
            return 0
        
        data = self._data_loader_._summary_data_
        index = self._data_loader_._summary_data_.keys()[0]
        col_names = self._data_loader_._summary_data_[index].keys()
        
        html = '<table border=\"1\" cellpadding=\"5\"><tr>'
        
        
        """ Build headers """
        html = html + '<th>' + self._data_loader_._query_type_ + '</th>'
        for i in col_names:
            html = html + '<th>' + i + '</th>'
        html = html + '</tr>'
        

        """ Build rows """
        for item in data.keys():
            html = html + '<tr>'
            html = html + '<td>' + item + '</td>'
            for elem in data[item].keys():
                html = html + '<td>' + str(data[item][elem]) + '</td>'
            html = html + '</tr>'
        
        html = html + '</table>'
        
        self._table_html_ = html
        


    """
        Use dataloader to produce object state - counts and times.  
        
        The general flow comprises of:
        
        <generate state> -> <post processing of state data> -> <generate plot>
        
        INPUT:
                start_time     - 
                end_time       - 
                interval       -
                query_type     -
                metric_name    -
                campaign       - 
                labels         -
         
    """        
    def run(self, start_time, end_time, interval, metric_name, campaign, labels):
        
        """ Execute the query that generates interval reporting data """
        return_val = self._data_loader_.run_query(start_time, end_time, interval, metric_name, campaign)
        self._counts_ = return_val[0]
        self._times_ = return_val[1]
        
        """ Select only the specified item keys """
        if len(self._item_keys_) > 0:
            self._counts_ = self.select_metric_keys(self._counts_)
            self._times_ = self.select_metric_keys(self._times_)
        
        """ Convert Times to Integers that indicate relative times AND normalize the intervals in case any are missing """
        for key in self._times_.keys():
            self._times_[key] = TP.normalize_timestamps(self._times_[key], False, 2)
            self._times_[key], self._counts_[key] = TP.normalize_intervals(self._times_[key], self._counts_[key], interval)
        
        """ Normalize times """
        
        min_time = min(self._times_)
        ranges = [min_time, 0]
        
        xlabel = 'MINUTES'
        subplot_index = 111
        fname = campaign + '_' + self._data_loader_._query_type_ + '_' + metric_name
        
        metric_full_name = QD.get_metric_full_name(metric_name)
        title = campaign + ':  ' + metric_full_name + ' -- ' + TP.timestamp_convert_format(start_time,1,2) + ' - ' + TP.timestamp_convert_format(end_time,1,2)
        ylabel = metric_full_name
        
        """ Determine List maximums -- Pre-processing for plotting """
        times_max = 0
        metrics_max = 0
        
        for key in self._counts_.keys():
            list_max = max(self._counts_[key])
            if list_max > metrics_max:
                metrics_max = list_max 
        
        for key in self._times_.keys():
            list_max = max(self._times_[key])
            if list_max > times_max:
                times_max = list_max
        
        ranges = list()
        ranges.append(0.0)
        ranges.append(times_max * 1.1)
        ranges.append(0.0)
        ranges.append(metrics_max * 1.1)
        
        """ Generate plots given data """
        self._gen_plot(self._counts_, self._times_, title, xlabel, ylabel, ranges, subplot_index, fname, labels)
        
        """ Generate table html """
        # self._write_html_table()

    
    
    
"""

    CLASS :: ConfidenceReporting
    
    Reports confidence values on specified metrics
    
    Types of queries supported:
    
        report_banner_confidence
        report_LP_confidence

"""
class ConfidenceReporting(DataReporting):
    
    _hypothesis_test_ = None
    
    
    """
    
        Constructor for confidence reporting class
        
        INPUT:
        
            hypothesis_test    - an instance reflecting the type of test being used
            
        
    """
    def __init__(self, **kwargs):
        
        
        for key in kwargs:
            
            if key == 'hyp_test':                          # Set the hypothesis test
                if kwargs[key] == 't_test':
                    self._hypothesis_test_ = HT.TTest()
        
        # print self._hypothesis_test_.__str__
        
        self._data_loader_ = DL.HypothesisTestLoader()
        DataReporting.__init__(self, **kwargs)
        
    """
        Describes how to run a report !! MODIFY !!
    """    
    def usage(self): 
        
        print ''
        
        return
    
    
    """
        <description>
        
        INPUT:
                        
        RETURN:
             
    """
    def _gen_plot(self,means_1, means_2, std_devs_1, std_devs_2, times_indices, title, xlabel, ylabel, ranges, subplot_index, labels, fname):
                
        pylab.subplot(subplot_index)
        pylab.figure(num=None,figsize=[26,14])    
        
        e1 = pylab.errorbar(times_indices, means_1, yerr=std_devs_1, fmt='xb-')
        e2 = pylab.errorbar(times_indices, means_2, yerr=std_devs_2, fmt='dr-')
        # pylab.hist(counts, times)
        
        """ Set the figure and font size """
        fig_width_pt = 246.0  # Get this from LaTeX using \showthe\columnwidth
        inches_per_pt = 1.0/72.27               # Convert pt to inch
        golden_mean = (math.sqrt(5)-1.0)/2.0         # Aesthetic ratio
        fig_width = fig_width_pt*inches_per_pt  # width in inches
        fig_height = fig_width*golden_mean      # height in inches
        fig_size =  [fig_width,fig_height]
        
        font_size = 20
        
        params = { 'axes.labelsize': font_size,
          'text.fontsize': font_size,
          'xtick.labelsize': font_size,
          'ytick.labelsize': font_size,
          'legend.pad': 0.1,     # empty space around the legend box
          'legend.fontsize': font_size,
          'font.size': font_size,
          'text.usetex': False,
          'figure.figsize': fig_size}
        
        pylab.rcParams.update(params)
        
        pylab.grid()
        pylab.ylim(ranges[2], ranges[3])
        pylab.xlim(ranges[0], ranges[1])
        pylab.legend([e1[0], e2[0]], labels,loc=2)
        
        pylab.xlabel(xlabel)
        pylab.ylabel(ylabel)
        
        pylab.title(title)
        pylab.savefig(self._file_path_ + fname + '.' + self._fig_file_format_, format=self._fig_file_format_)
    
    
    def _gen_box_plot(self, data, title, ylabel, subplot_index, labels, fname):
                
        print data
        print labels
        
        pylab.subplot(subplot_index)
        pylab.figure(num=None,figsize=[26,14])    
        
        bp = pylab.boxplot(data)
        pylab.xticks(range(1, len(labels) + 1), labels)
        
        """ Set the figure and font size """
        fig_width_pt = 246.0  # Get this from LaTeX using \showthe\columnwidth
        inches_per_pt = 1.0/72.27               # Convert pt to inch
        golden_mean = (math.sqrt(5)-1.0)/2.0         # Aesthetic ratio
        fig_width = fig_width_pt*inches_per_pt  # width in inches
        fig_height = fig_width*golden_mean      # height in inches
        fig_size =  [fig_width,fig_height]
        
        font_size = 20
        
        params = { 'axes.labelsize': font_size,
          'text.fontsize': font_size,
          'xtick.labelsize': font_size,
          'ytick.labelsize': font_size,
          'legend.pad': 0.1,     # empty space around the legend box
          'legend.fontsize': font_size,
          'font.size': font_size,
          'text.usetex': False,
          'figure.figsize': fig_size}
        
        pylab.rcParams.update(params)
        
        pylab.grid()
        #pylab.ylim(ranges[2], ranges[3])
        #pylab.xlim(ranges[0], ranges[1])
        # pylab.legend([e1[0], e2[0]], labels,loc=2)
        
        pylab.ylabel(ylabel)
        
        pylab.title(title)
        pylab.savefig(self._file_path_ + fname + '.' + self._fig_file_format_, format=self._fig_file_format_)
        
    """ 
        Print in Tabular form the means and standard deviation of each group over each 
        interval
        
        INPUT:
                        
        RETURN: 
        
    """
    def print_metrics(self, filename, metric_name, means_1, means_2, std_devs_1, std_devs_2, times_indices, labels, test_call):
        
        filename += '.txt'
        file = open(self._file_path_ + filename, 'w')
        
        """ Compute % increase and report """
        av_means_1 = sum(means_1) / len(means_1)
        av_means_2 = sum(means_2) / len(means_2)
        percent_increase = math.fabs(av_means_1 - av_means_2) / min(av_means_1,av_means_2) * 100.0
        
        """ Compute the average standard deviations """
        av_std_dev_1 = 0
        av_std_dev_2 = 0
        
        for i in range(len(std_devs_1)):
            av_std_dev_1 = av_std_dev_1 + math.pow(std_devs_1[i], 2)
            av_std_dev_2 = av_std_dev_2 + math.pow(std_devs_2[i], 2)
        
        av_std_dev_1 = math.pow(av_std_dev_1 / len(std_devs_1), 0.5) 
        av_std_dev_2 = math.pow(av_std_dev_2 / len(std_devs_1), 0.5) 
        
        """ Assign the winner """    
        if av_means_1 > av_means_2:
            winner = labels[0]
        else:
            winner = labels[1]
            
        win_str =  '\nThe winner "' + winner + '" had a %.2f%s increase.'
        win_str = win_str % (percent_increase, '%')
        
        #print '\nCOMMAND = ' + test_call
        file.write('\nCOMMAND = ' + test_call)
                 
         
#        print  '\n\n' +  metric_name 
#        print '\nitem 1  = ' + labels[0] 
#        print 'item 2  = ' + labels[1]
#        print win_str
#        print '\ninterval\tmean1\t\tmean2\t\tstddev1\t\tstddev2\n'
        file.write('\n\n' +  metric_name)
        file.write('\nitem 1  = ' + labels[0] + '\n')
        file.write('\nitem 2  = ' + labels[1] + '\n')
        file.write(win_str)
        file.write('\n\ninterval\tmean1\t\tmean2\t\tstddev1\t\tstddev2\n\n')
        
        
        """ Print out the parameters for each interval """
        
        for i in range(len(times_indices)):
            line_args = str(i) + '\t\t' + '%.5f\t\t' + '%.5f\t\t' + '%.5f\t\t' + '%.5f\n'
            line_str = line_args % (means_1[i], means_2[i], std_devs_1[i], std_devs_2[i])
#            print  line_str
            file.write(line_str)
        
        """ Print out the averaged parameters """
        line_args = '%.5f\t\t' + '%.5f\t\t' + '%.5f\t\t' + '%.5f\n'
        line_str = line_args % (av_means_1, av_means_2, av_std_dev_1, av_std_dev_2)
        
#        print '\n\nOverall Parameters -- the confidence test was run with these parameters:\n'
#        print '\nmean1\t\tmean2\t\tstddev1\t\tstddev2\n'
#        print line_str
        
        file.write('\n\nOverall Parameters:\n')
        file.write('\nmean1\t\tmean2\t\tstddev1\t\tstddev2\n')
        file.write(line_str)
                        
        file.close()
        
        return [winner, percent_increase]
    
    """ 
        Executes the test reporting
        
        INPUT:
                        
        RETURN:
        
    """
    def run(self, test_name, query_name, metric_name, campaign, items, start_time, end_time, interval, num_samples):
        
        """ TEMPORARY - map items and labels, this should be more generalized """
        counter = 1
        for key in items.keys():
            if counter == 1:
                item_1 = items[key]
                label_1 = key
            elif counter == 2:
                item_2 = items[key]
                label_2 = key
            counter += 1
                
        """ Retrieve values from database """
        ret = self._data_loader_.run_query(query_name, metric_name, campaign, item_1, item_2, start_time, end_time, interval, num_samples)
        metrics_1 = Hlp.convert_Decimal_list_to_float(ret[0])
        metrics_2 = Hlp.convert_Decimal_list_to_float(ret[1])
        times_indices = ret[2]
        
        """ run the confidence test """
        ret = self._hypothesis_test_.confidence_test(metrics_1, metrics_2, num_samples)
        means_1 = ret[0]
        means_2 = ret[1]
        std_devs_1 = ret[2]
        std_devs_2 = ret[3]
        confidence = ret[4]
    
        """ plot the results """
        xlabel = 'Hours'
        subplot_index = 111
        fname = campaign + '_conf_' + metric_name
        
        title = confidence + '\n\n' + test_name + ' -- ' + TP.timestamp_convert_format(start_time,1,2) + ' - ' + TP.timestamp_convert_format(end_time,1,2)
        
        max_mean = max(max(means_1),max(means_2))
        max_sd = max(max(std_devs_1),max(std_devs_2))
        max_y = float(max_mean) + float(max_sd) 
        max_y = max_y + 0.1 * max_y
        max_x = max(times_indices) + min(times_indices)
        ranges = [0.0, max_x, 0, max_y]
        
        ylabel = QD.get_metric_full_name(metric_name)
        labels = [label_1, label_2]
        
        # self._gen_plot(means_1, means_2, std_devs_1, std_devs_2, times_indices, title, xlabel, ylabel, ranges, subplot_index, labels, fname)
        self._gen_box_plot([metrics_1, metrics_2], title, ylabel, subplot_index, labels, fname)
        
        """ Print out results """ 
        test_call = "run('" + test_name + "', '" + query_name + "', '" + metric_name + "', '" + campaign + "', '" + \
            item_1 + "', '" + item_2 + "', '" + start_time + "', '" + end_time + "', " + str(interval) + ", " + str(num_samples) + ")"
        winner, percent_increase = self.print_metrics(fname, title, means_1, means_2, std_devs_1, std_devs_2, times_indices, labels, test_call)
        
        return [winner, percent_increase, confidence]





    