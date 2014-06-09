<?php
include_once("../include/common.php");
include_once("FileDownloader.php");
$content = <<<EOF
package com.taobao.ju.common.dal.dao;

import java.util.List;
import java.util.Map;

import com.alibaba.common.logging.Logger;
import com.alibaba.common.logging.LoggerFactory;
import org.springframework.beans.factory.InitializingBean;
import org.springframework.dao.DataAccessException;
import org.springframework.jdbc.core.JdbcTemplate;
import org.springframework.orm.ibatis.SqlMapClientTemplate;

import com.taobao.tddl.client.sequence.Sequence;
import com.taobao.tddl.client.sequence.SequenceException;

/**
 * User: duxing
 * Email: duxing@taobao.com
 * Date: 2013-1-22
 */
public class BaseDAO implements InitializingBean {

    protected Logger logger = LoggerFactory.getLogger(this.getClass());

	protected SqlMapClientTemplate sqlMapClient;

	protected Map<String,Sequence> sequenceTable;

	protected JdbcTemplate jdbcTemplate;

	@Override
	public void afterPropertiesSet() throws Exception {
		if(sqlMapClient==null||sequenceTable==null)
			throw new Exception("BaseDAO initilize fail,check related spring's configuration file");
	}

	public void setSqlMapClient(SqlMapClientTemplate sqlMapClient) {
		this.sqlMapClient = sqlMapClient;
	}


	public void setSequenceTable(Map<String, Sequence> sequenceTable) {
		this.sequenceTable = sequenceTable;
	}

	public void setJdbcTemplate(JdbcTemplate jdbcTemplate) {
		this.jdbcTemplate = jdbcTemplate;
	}
    //ID_KEY的内部enum类请各自应用放入自己的代码
	protected Long getNextId(String idKey) throws DAOException {
		if(idKey == null) throw new IllegalArgumentException("idKey can not be null");
		Sequence sequence =sequenceTable.get(idKey+"_sequence");
		if(sequence==null) throw new IllegalStateException(idKey+"'s sequence not found");
		try {
			return sequence.nextValue();
		} catch (SequenceException e) {
			throw new DAOException("[BaseDAO-getNextId]",e);
		}
	}

    public int update(String statementName, Object parameterObject) throws DAOException {
		try{
            return sqlMapClient.update(statementName, parameterObject);
        }catch (DataAccessException e){
            throw new DAOException("[BaseDAO-update]",e);
        }
	}

	public Object insert(String statementName, Object parameterObject) throws DAOException {
		try{
            return sqlMapClient.insert(statementName, parameterObject);
        }catch (DataAccessException e){
            throw new DAOException("[BaseDAO-insert]",e);
        }
	}

    public int delete(String statementName, Object parameterObject) throws DAOException {
		try{
            return sqlMapClient.delete(statementName, parameterObject);
        }catch (DataAccessException e){
            throw new DAOException("[BaseDAO-delete]",e);
        }
	}

	public Object queryForObject(String statementName, Object parameterObject) throws DAOException {
		try{
            return sqlMapClient.queryForObject(statementName, parameterObject);
        }catch (DataAccessException e){
            throw new DAOException("[BaseDAO-queryForObject]",e);
        }
	}

	public List<?> queryForList(String statementName, Object parameterObject) throws DAOException {
		try{
            return sqlMapClient.queryForList(statementName, parameterObject);
        }catch (DataAccessException e){
            throw new DAOException("[BaseDAO-queryForList]",e);
        }
	}

    /**
     * 取List，包含分页
     *
     * @param statementName
     * @param parameterObject
     * @param pageNo
     *             页次
     * @param pageSize
     *             每页记录数
     * @return
     * @throws DAOException
     * @author zhengqing
     */
    public List<?> queryForList(String statementName, Object parameterObject, int pageNo, int pageSize) throws DAOException {
        try{
            return sqlMapClient.queryForList(statementName, parameterObject, pageSize * (pageNo - 1), pageSize);
        }catch (DataAccessException e){
            throw new DAOException("[BaseDAO-queryForList]",e);
        }
    }

	public Map<?, ?> queryForMap(String statementName, Object parameterObject, String keyProperty) throws DAOException {
		try{
            return sqlMapClient.queryForMap(statementName, parameterObject, keyProperty);
        }catch (DataAccessException e){
            throw new DAOException("[BaseDAO-queryForMap]",e);
        }
	}

}
EOF;

FileDownloader::download("BaseDAO.java",$content);
