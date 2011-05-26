
"""

This module is used to define the reporting methodologies on different types of data.  The base class
DataReporting is defined to outline the general functionality of the reporting architecture and 
functionality which includes generating the data via a dataloader object and transforming the data
among different reporting mediums including matlab plots (primary medium) and html tables.

The DataLoader class decouples the data access of the reports using the Adapter structural pattern.

"""

__author__ = "Ryan Faulkner"
__revision__ = "$Rev$"
__date__ = "April 16th, 2011"


import sys
# sys.path.append('../')

import math
import datetime as dt
import MySQLdb
import pylab
import matplotlib

import Fundraiser_Tools.classes.Helper as mh
import Fundraiser_Tools.classes.QueryData as QD
import Fundraiser_Tools.classes.DataLoader as DL
import Fundraiser_Tools.classes.TimestampProcessor as TP

matplotlib.use('Agg')


"""

    CLASS :: HypothesisTest
    
    
    METHODS:
        confidence_test        - defined in subclasses, performs the test
        compute_parameters    - computes parameters of models for test
        
"""
class HypothesisTest(object):

    """ 
        Assess the confidence of the winner - define in subclass
        
        INPUT:
                        
        RETURN:
        
    """
    def confidence_test(self, metrics_1, metrics_2, time_indices, num_samples):
        return
        

    """ 
        Determine the parameters of the distribution
        
        INPUT:
                        
        RETURN:
        
    """
    def compute_parameters(self, metrics_1, metrics_2, num_samples):
        
        # A trial represents a group of samples over which parameters are computed 
        num_trials = int(math.ceil(float(len(metrics_1)) / float(num_samples)))
        
        means_1 = []
        means_2 = []
        vars_1 = []
        vars_2 = []
        
        m_tot = 0
        sd_tot = 0
        
        # Compute the mean and variance for each group across all trials
        for i in range(num_trials):
            
            m1 = 0        # mean of group 1
            m2 = 0        # mean of group 2
            var1 = 0        # variance of group 1
            var2 = 0        # variance of group 2
                
            for j in range(num_samples):
                index = i  * num_samples + j
        
                # Compute mean for each group
                m1 = m1 + metrics_1[index]
                m2 = m2 + metrics_2[index]
            
            m1 = m1 / num_samples
            m2 = m2 / num_samples
            
            # Compute Sample Variance for each group
            for j in range(num_samples):
                index = i + j
                
                var1 = var1 + math.pow((metrics_1[i] - m1), 2) 
                var2 = var2 + math.pow((metrics_2[i] - m2), 2)
            
            means_1.append(float(m1))
            means_2.append(float(m2))
            vars_1.append(var1 / num_samples)
            vars_2.append(var2 / num_samples)
        
        return [num_trials, means_1, means_2, vars_1, vars_2]


    
        
"""

Class :: WaldTest

Implements a Wald test where the distribution of donations over a given period are 
assumed to be normal

http://en.wikipedia.org/wiki/Wald_test

"""
class WaldTest(HypothesisTest):
    
    """ 
        <description>
        
        INPUT:
                        
        RETURN:
        
    """ 
    def confidence_test(self, metrics_1, metrics_2, num_samples):
        
        ret = self.compute_parameters(metrics_1, metrics_2, num_samples)
        num_trials = ret[0]
        means_1 = ret[1]
        means_2 = ret[2]
        vars_1 = ret[3]
        vars_2 = ret[4]
        
        """ Compute std devs """
        std_devs_1 = []
        std_devs_2 = []
        for i in range(len(vars_1)):
            std_devs_1.append(math.pow(vars_1[i], 0.5))
            std_devs_2.append(math.pow(vars_2[i], 0.5))
        
        m_tot = 0
        sd_tot = 0
        
        # Compute the parameters for the Wald test
        # The difference of the means and the sum of the variances is used to compose the random variable W = X1 - X2 for each trial
        # where X{1,2} is the random variable corresponding to the group {1,2}
        for i in range(num_trials):
            
            # Perform wald - compose W = X1 - X2 for each trial
            sd = math.pow(vars_1[i] + vars_2[i], 0.5)
            m = math.fabs(means_1[i] - means_2[i]) 
            
            m_tot  = m_tot + m
            sd_tot  = sd_tot + sd
            
            
        W = m_tot / sd_tot
        # print W
            
        # determine the probability that the 
        if (W >= 1.9):
            conf_str = '95% confident about the winner.'
            P = 0.95
        elif (W >= 1.6):
            conf_str = '89% confident about the winner.'
            P = 0.89
        elif (W >= 1.3):
            conf_str = '81% confident about the winner.'
            P = 0.81
        elif (W >= 1.0):
            conf_str = '73% confident about the winner.'
            P = 0.73
        elif (W >= 0.9):
            conf_str = '68% confident about the winner.'
            P = 0.68
        elif (W >= 0.8):
            conf_str = '63% confident about the winner.'
            P = 0.63
        elif (W >= 0.7):
            conf_str = '52% confident about the winner.'
            P = 0.52
        elif (W >= 0.6):
            conf_str = '45% confident about the winner.'
            P = 0.45
        elif (W >= 0.5):
            conf_str = '38% confident about the winner.'
            P = 0.38
        elif (W >= 0.4):
            conf_str = '31% confident about the winner.'
            P = 0.31
        elif (W >= 0.3):
            conf_str = '24% confident about the winner.'
            P = 0.24
        elif (W >= 0.2):
            conf_str = '16% confident about the winner.'
            P = 0.16
        elif (W >= 0.1):
            conf_str = '8% confident about the winner.'
            P = 0.08
        else:
            conf_str = 'There is no clear winner.'
            P = 0.08
    
        
        return [means_1, means_2, std_devs_1, std_devs_2, conf_str]
        

"""

Class :: TTest

Implements a Student's T test where the distribution of donations over a given period are 
assumed to resemble those of a students t distribution

http://en.wikipedia.org/wiki/Student%27s_t-test

"""
class TTest(HypothesisTest):
    
    _data_loader_ = None
    
    """ 
        <description>
        
        INPUT:
                        
        RETURN:
        
    """ 
    def __init__(self):
        self._data_loader_ = DL.TTestLoaderHelp()
    
    """ 
        <description>
        
        INPUT:
                        
        RETURN:
        
    """ 
    def confidence_test(self, metrics_1, metrics_2, num_samples):
        
        """ retrieve means and variances """
        ret = self.compute_parameters(metrics_1, metrics_2, num_samples)
        num_trials = ret[0]
        means_1 = ret[1]
        means_2 = ret[2]
        vars_1 = ret[3]
        vars_2 = ret[4]
        
        """ Compute std devs """
        std_devs_1 = []
        std_devs_2 = []
        for i in range(len(vars_1)):
            std_devs_1.append(math.pow(vars_1[i], 0.5))
            std_devs_2.append(math.pow(vars_2[i], 0.5))
            
        m_tot = 0
        var_1_tot = 0
        var_2_tot = 0
        
        """ Compute the parameters for the student's t-test
             The difference of the means and the sum of the variances is used to compose the random variable W = X1 - X2 for each trial
            where X{1,2} is the random variable corresponding to the group {1,2} """
        for i in range(num_trials): 
        
            m_tot  = m_tot + math.fabs(means_1[i] - means_2[i])
            var_1_tot  = var_1_tot + vars_1[i]
            var_2_tot  = var_2_tot + vars_2[i]
            
        m = m_tot / num_trials
        s_1 = var_1_tot / num_trials
        s_2 = var_2_tot / num_trials
        
        total_samples = len(metrics_1)
        
        t = m / math.pow((s_1 + s_2) / total_samples, 0.5)
        degrees_of_freedom = (math.pow(s_1 / total_samples + s_2 / total_samples, 2) / (math.pow(s_1 / total_samples, 2) + math.pow(s_2 / total_samples, 2))) * total_samples
            
        
        """ lookup confidence """
        # get t and df
        degrees_of_freedom = math.ceil(degrees_of_freedom)
        if degrees_of_freedom > 30:
            degrees_of_freedom = 99
        
        p = self._data_loader_.get_pValue(degrees_of_freedom, t)
        
        """ Determine confidence range """
        probs = [0.400000, 0.250000, 0.100000, 0.050000, 0.025000, 0.010000, 0.005000, 0.000500]
        prob_diffs = [math.fabs(i-p) for i in probs]
        min_index = min((n, i) for i, n in enumerate(prob_diffs))[1]
        
        if min_index > 0:
            lower_p = probs[min_index - 1]
        
        conf_str =  'Between ' + str((1 - lower_p) * 100) + '% and ' + str((1 - p) * 100) + '% confident about the winner.'
        
        return [means_1, means_2, std_devs_1, std_devs_2, conf_str]
        
"""

Class :: ChiSquareTest

Implements a Chi Square test where the distribution of donations over a given period are 
assumed to resemble those of a students t distribution

http://en.wikipedia.org/wiki/Chi-square_test

"""
class ChiSquareTest(HypothesisTest):
    
    """ 
        <description>
        
        INPUT:
                        
        RETURN:
        
    """ 
    def confidence_test(self, metrics_1, metrics_2, num_samples):
        return
    